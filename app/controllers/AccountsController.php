<?php

namespace app\controllers;

use MongoDate;
use MongoId;
use DateTime;

use app\models\Banks;
use app\models\Accounts;
use app\models\Transactions;
use app\models\Users;

use lithium\security\Auth;

use li3_flash\extensions\storage\Flash;

class AccountsController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();
	
	// Enable 'negotiate' so that AJAX requests are answered correctly
	protected function _init() {
		$this->_render['negotiate'] = true;
		parent::_init();
	}
	
	public function index() {
		$this->redirect('/accounts/summary');
	}

	// Stream of transactions.
	public function summary($year = null, $month = null) {
		$date = ($year === null || $month === null) ? null : $year.'/'.$month;
		$accounts = $this->_currentUser->accounts();

		if (count($accounts) < 1) {
			return $this->redirect('/accounts/add');
		}

		$periods = Accounts::periods($accounts);
		$transactions = array();
		$unverifiedCount = 0;

		if (!empty($periods)) {
			if (empty($date) || !in_array($date, $periods)) {
				$date = $periods[count($periods) - 1];
			}
			
			$start = strtotime($date.'/01');
			$end = strtotime('last day of 23:59:59', $start);
			
		}
		
		if ($this->request->is('ajax')) {
			$transactions = $this->_currentUser->transactionsForPeriod($start, $end);
			$unverifiedCount = Transactions::countUnverified($transactions);
		}

		$this->set(compact('transactions', 'periods', 'date', 'unverifiedCount'));
	}

	// Add a new account to a user profile.
	public function add() {
		$bankList = Banks::find('list');
		$accountList = array();

		if (!empty($this->request->data)) {
			if (!empty($this->request->data['portal']['login']) && !empty($this->request->data['portal']['password'])) {
				$bank = Banks::first($this->request->data['bank_id']);

				$connector = Accounts::createConnector(
					$bank->short_title,
					$this->request->data['portal']['login'],
					$this->request->data['portal']['password']
				);
				
				if (!empty($connector)) {
					if (empty($this->request->data['id'])) {
						$connector->scrape();
						$accounts = $connector->getAccounts();
						$accountList = $bank->filterAccountList(&$accounts);

						foreach ($accounts as $acc) {
							$accountList[$acc['id']] = $acc['title'].' '.(string) $acc['id'];
						}
					}
					else {
						$connector->scrape($this->request->data['id']);

						$header = $this->request->data + $connector->getHeader();
						$header['portal']['lastAccess'] = time();
						$account = Accounts::create($header);

						if ($account->save()) {
							$account->inputTransactions($connector->getTransactions());

							Flash::write('Compte bancaire ajouté avec succès!', array('class' => 'success'));
							return $this->redirect('accounts/summary');
						}
					}
				}
			}
		}
		
		if (empty($account)) {
			$account = Accounts::create($this->request->data);
			$account->user_id = $this->_currentUser->_id;
		}
		
		$this->set(compact('account', 'accountList', 'bankList'));
	}

	public function refresh() {
		if (empty($this->request->params['id'])) {
			return $this->redirect('/accounts/summary');
		}

		$account = $this->_currentUser->account($this->request->params['id']);

		if (!empty($account)) {
			$transactions = $account->fetchTransactions();

			if (!empty($transactions)) {
				$account->inputTransactions($transactions);
			}

			$account->refresh();
			$account->save();
		}

		return $this->redirect('/accounts/summary');
	}

	/**
	 * Remove an account
	 * 
	 * @param string $id ObjectID of the account to be removed.
	 * @todo Set the account to disabled, and archive the data instead?
	 */
	public function delete() {
		if (empty($this->request->params['id'])) {
			Flash::write('Aucun compte bancaire à supprimer n\a été trouvé.', array('class' => 'error'));
			return $this->redirect('/users/view');
		}

		$account = $this->_currentUser->account($this->request->params['id']);

		if (isset($this->request->data['userValidation'])) {
			if ($this->request->data['userValidation'] == true && !empty($account)) {
				$this->_currentUser->deleteAccount($this->request->params['id']);
				
				Flash::write('Le compte '.$account->title.' a été supprimé.', array('class' => 'success'));
				return $this->redirect('/users/view');
			}
			else {
				Flash::write('Le compte '.$account->title.'n\'a pas été supprimé.', array('class' => 'error'));
				return $this->redirect('/users/view');
			}
		}

		$this->set(compact('account'));
	}
}
?>