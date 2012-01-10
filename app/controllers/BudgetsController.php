<?php

namespace app\controllers;

use app\models\Users;
use app\models\Categories;
use app\models\Budgets;
use app\models\Accounts;

use lithium\util\Set;

use li3_flash\extensions\storage\Flash;

class BudgetsController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();

	protected function _init() {
		$this->_render['negotiate'] = true;
		parent::_init();
	}
	
	public function details() {
		$budget = Budgets::first(
			array(
				'conditions' => array(
					'user_id' => (string) $this->_currentUser->_id
				)
			)
		);

		if (empty($budget)) {
			return $this->redirect('budgets/add');
		}
		
		$categories = Categories::parents();

		$this->set(compact('categories', 'budget'));
	}
	
	public function edit() {
	}
	
	public function add() {
		if (!empty($this->request->data)) {
			$this->request->data['user_id'] = (string) $this->_currentUser->_id;

			$budget = Budgets::create($this->request->data);
			$budget->cleanCategories();
			$budget->cleanTags();

			if ($budget->validates()) {
				$budget->refresh();

				if ($budget->save()) {
					Flash::write('Budget créé avec succès!', array('class' => 'success'));
					return $this->redirect('budgets/details');
				}
				else {
					Flash::write('La création de budget a échouée!', array('class' => 'error'));
					return $this->redirect('budgets/add');
				}
			}
		}

		if (empty($budget)) {
			$categories = array();
			foreach ($this->_categories as $cat) {
				$categories[] = array(
					'category_id' => (string) $cat->_id,
					'category_title' => $cat->title,
					'limit' => 0,
					'suggestedAmount' => Budgets::suggestedAmount(
						(string) $cat->_id,
						(string) $this->_currentUser->_id
					)
				);
			}

			$data = Set::merge($this->request->data, compact('categories'));
			$budget = Budgets::create($data);	
		}
		
		$this->set(compact('budget'));
	}

	public function delete() {
		if (empty($this->request->params['id'])) {
			Flash::write('Aucun compte bancaire à supprimer trouvé.', array('class' => 'notice'));
			return $this->redirect('accounts/summary');
		}

		$this->_currentUser->deleteBudget($this->request->params['id']);
		
		Flash::write('Le budget '.$this->request->params['id'].' a été supprimé.', array('class' => 'success'));
		
		return $this->redirect('/accounts/summary');
	}
	
	public function refresh() {
		if (empty($this->request->params['id'])) {
			Flash::write('Aucun budget trouvé.', array('class' => 'notice'));
			return $this->redirect('budgets/details');
		}
		
		$budget = $this->_currentUser->getBudget();

		if (!empty($budget)) {
			$budget->refresh();
			$budget->save();
		}

		return $this->redirect('/budgets/details');
	}

	public function getMonthChart() {
		if (!$this->request->is('ajax')) {
			return $this->redirect('/budgets/details');
		}

		$budget = $this->_currentUser->getBudget();
		$data = $budget->monthlyPosition();

		$this->set($data);
	}

	public function getMonthLimits() {
		if (!$this->request->is('ajax')) {
			return $this->redirect('/budgets/details');
		}

		$budget = $this->_currentUser->getBudget();
		$data = $budget->monthLimits();

		$this->set($data);
	}

	public function getHistory() {
		if (!$this->request->is('ajax')) {
			return $this->redirect('/budgets/details');
		}

		$budget = $this->_currentUser->getBudget();
		$data = $budget->history();

		$this->set($data);
	}
}
?>