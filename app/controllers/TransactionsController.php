<?php

namespace app\controllers;

use MongoDate;
use MongoId;
use DateTime;

use app\models\Accounts;
use app\models\Categories;
use app\models\Files;
use app\models\Transactions;
use app\models\Users;

use lithium\security\Auth;

use li3_flash\extensions\storage\Flash;

class TransactionsController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();
	
	protected function _init() {
		$this->_render['negotiate'] = true;
		parent::_init();
	}
	
	public function view() {
		if (empty($this->request->params['id'])) {
			return $this->redirect('/accounts/summary');
		}
		
		$transaction = $this->_currentUser->transaction($this->request->params['id']);
		
		if (empty($transaction)) {
			return $this->redirect('/accounts/summary');
		}

		$files = $transaction->getAssociatedFiles();
		
		$this->set(compact('transaction', 'files'));
	}

	public function getAssociatedFiles() {
		if (empty($this->request->params['id']) || !$this->request->is('ajax')) {
			return $this->redirect('/accounts/summary');
		}

		$files = $this->_currentUser->transaction($this->request->params['id'])->getAssociatedFiles();

		$this->set(compact('files'));
	}

	public function multipleEdit() {
		if (empty($this->request->data['_id']) || empty($this->request->data) || !$this->request->is('ajax')) {
			return $this->redirect('/accounts/summary');
		}

		$transactions = $this->_currentUser->transactionsById($this->request->data['_id']);

		foreach ($transactions as $transaction) {
			$this->_updateTransaction(&$transaction);
		}

		$this->set(array('transaction' => $transactions));
	}

	public function edit() {
		if (empty($this->request->params['id'])) {
			return $this->redirect('/accounts/summary');
		}

		$transaction = $this->_currentUser->transaction($this->request->params['id']);
		
		if (empty($transaction)) {
			return $this->redirect('/accounts/summary');
		}
		
		if (!empty($this->request->data)) {
			if ($this->_updateTransaction($transaction)) {
				if (!$this->request->is('ajax')) {
					Flash::write('Transaction modifiée avec succès!', array('class' => 'success'));
					return $this->redirect(
						array(
							'controller' => 'transactions',
							'action' => 'view',
							'args' => (string) $transaction->_id
						)
					);
				}
			}
			else {
				Flash::write('La modification a échouée!', array('class' => 'error'));
			}
		}

		$files = $transaction->getAssociatedFiles();
		$categoryList = Categories::find('list', array('conditions' => array('parent' => '')));
		
		$this->set(compact('transaction', 'categoryList', 'files'));
	}

	protected function _updateTransaction(&$transaction) {
		$data = array();
		$categoryChange = false;
		$tagsChange = false;

		/*if (isset($this->request->data['meta']['tags'])) {
			$data += array('meta.tags' => explode(',', $this->request->data['meta']['tags']));
		}*/

		if (isset($this->request->data['meta']['note'])) {
			$data += array('meta.note' => trim($this->request->data['meta']['note']));
			$tagsChange = true;
		}

		if (isset($this->request->data['meta']['verified'])) {
			$data += array('meta.verified' => (strcmp($this->request->data['meta']['verified'], '1') == 0) ? true : false);
		}

		$transaction->set($data);
		
		if (isset($this->request->data['fileUpload'])) {
			$file = $this->request->data['fileUpload'];
			unset($this->request->data['fileUpload']);
		}

		if (isset($this->request->data['meta']['category_id'])) {
			$categoryChange = $transaction->applyCategory($this->request->data['meta']['category_id'], array('propagate' => false));
		}

		if ($transaction->validates()) {
			if (!empty($file['tmp_name'])) {
				$transaction->associateFile(array(
					'file' => $file,
					'user_id' => $transaction->user_id,
					'transaction_id' => (string) $transaction->_id
				));
			}
			
			if ($this->request->data['propagateChanges']) {
				if ($categoryChange) {
					$propagationResults = $transaction->propagateCategory();
					$this->set(compact('propagationResults'));
				}

				if ($tagsChange) {
					$transaction->updateTags();
					$this->_currentUser->refreshTags();
				}
			}
			
			return $transaction->save();
		}

		return false;
	}
}
?>