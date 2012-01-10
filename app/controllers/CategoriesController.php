<?php

namespace app\controllers;

use app\models\Users;
use app\models\Categories;
use app\models\Budgets;
use app\models\Accounts;
use app\models\Transactions;

use li3_flash\extensions\storage\Flash;

class CategoriesController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();

	public function index($date = null) {
		$categories = array();

		if ($this->request->is('ajax')) {
			$categories = $this->_currentUser->categories();
		}
		
		$periods = Accounts::periods($this->_currentUser->accounts());

		if (!empty($periods) && (empty($date) || !in_array($date, $periods))) {
			$date = $periods[count($periods) - 1];
		}
		
		$this->set(compact('categories', 'date'));
	}
	
	public function details() {
		$id = $this->request->params['id'];
		
		if (empty($id)) {
			return $this->redirect('categories/all');
		}
		
		$category = Categories::first($id);
		
		$transactions = Transactions::all(
			array(
				'conditions' => array(
					'meta.category_id' => $id
				)
			)
		);
		
		$this->set(compact('transactions', 'category'));
	}
	
	/*public function initialise() {
		Categories::initialise();
		return $this->redirect('/accounts/summary');
	}*/

	public function getCategories() {
		if (!$this->request->is('ajax') || $this->request->type() != 'json') {
			return $this->redirect('/accounts/summary');
		}
		
		$this->set(array('categories' => $this->_categories->to('array')));
	}
}
?>