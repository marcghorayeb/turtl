<?php

namespace app\controllers;

use app\models\Banks;

class BanksController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();
	
	public function initialise() {
		Banks::initialise();
		$banks = Banks::all();
		print_r($banks->data());
		die;
	}

	public function index() {
		$banks = Banks::all();
		$this->set(compact('banks'));
	}

	public function edit() {
		if (!$this->request->params['id']) {
			return $this->redirect('/banks/');
		}

		$bank = Banks::first($this->request->params['id']);

		if (!empty($this->request->data)) {
			$bank->set($this->request->data);
			$bank->cleanTemplates();
			$bank->save();
		}

		$this->set(compact('bank'));
	}
}
?>