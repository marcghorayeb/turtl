<?php

namespace app\extensions\data;

abstract class Connector extends \lithium\core\Object {
	protected $_autoConfig = array(
		'bank',
		'login',
		'password'
	);

	protected $_scripts = array(
		'SG' => '/home/admin/tools/connectors/sg.py'
	);

	protected $_results = array();

	protected $_accounts = array();

	protected $_header = array();

	protected $_transactions = array();

	protected function _init() {
		parent::_init();
	}

	public function setCredentials($login, $password) {
		$this->_config['login'] = $login;
		$this->_config['password'] = $password;
	}

	public function scrape($account = null) {
		$this->_scrape($account);

		if (!empty($this->_results)) {
			if ($account) {
				$this->_filterHeader();
				$this->_filterTransactions();
			}
			else {
				$this->_filterAccounts();
			}

			$this->_cleanInput();
		}
	}

	public function getResults() {
		return $this->_results;
	}

	public function getAccounts() {
		return $this->_accounts;
	}

	public function getHeader() {
		return $this->_header;
	}

	public function getTransactions() {
		return $this->_transactions;
	}

	public function __destruct() {
	}

	abstract protected function _scrape();

	abstract protected function _filterHeader();

	abstract protected function _filterTransactions();

	abstract protected function _cleanInput();
}
?>
