<?php

namespace app\extensions\data\connector;

use MongoDate;
use DateTime;

class SG extends \app\extensions\data\Connector {
	protected function _init() {
		parent::_init();

		$this->_config['bank'] = 'SG';
	}

	protected function _scrape($account = null) {
		$command = 'python '.$this->_scripts[$this->_config['bank']].' '.$this->_config['login'].' '.$this->_config['password'];

		if (!empty($account)) {
			$command .= ' '.$account;
		}

		exec($command, $this->_results);

		array_walk_recursive(
			$this->_results,
			function (&$value, $field) {
				$value = str_getcsv($value, ';');
			}
		);
	}

	protected function _filterAccounts() {
		$defaults = array(
			'balance.date' => ''
		);

		foreach ($this->_results as $result) {
			$data = array(
				'id' => $result[0],
				'title' => $result[1],
				'balance.amount' => floatval($result[2])
			);

			$this->_accounts[] = $data + $defaults;
		}
	}

	protected function _filterHeader() {
		$defaults = array(
			'defaultCurrency' => 'EUR',
			'balance.date' => '',
			'period.start' => '',
			'period.end' => '',
			'period.activityCount' => 0
		);

		foreach ($this->_results as $result) {
			if ($result[0] == 'A') {
				$this->_header = array(
					'id' => $result[1],
					'title' => $result[2],
					'balance.amount' => $result[3]
				);

				break;
			}
		}

		$this->_header += $defaults;
	}

	protected function _filterTransactions() {
		$defaults = array(
			'currency' => $this->_header['defaultCurrency'],
			'credit' => 0,
			'debit' => 0,
			'title' => '',
			'description' => ''
		);

		foreach ($this->_results as $result) {
			if ($result[0] == 'T') {
				$data = array(
					'date' => $result[1],
					'title' => $result[2],
					'description' => $result[2]
				);

				$amount = floatval($result[3]);

				$data['credit'] = ($amount > 0) ? $amount : 0;
				$data['debit'] = ($amount < 0) ? $amount : 0;

				$this->_transactions[] = $data + $defaults;
			}
		}
	}

	protected function _cleanInput() {
		$connector = $this;
		array_walk_recursive(
			$this->_header,
			function (&$value, $field) use ($connector) {
				switch ($field) {
					case "period.start":
						if (empty($value)) {
							$value = $connector->getFirstTransactionDate();
						}
					case "period.end":
						if (empty($value)) {
							$value = $connector->getLastTransactionDate();
						}
					case "balance.date":
						if (empty($value)) {
							$value = $connector->getLastTransactionDate();
						}

						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = $date->getTimeStamp();
						break;
					case "period.activityCount":
						if (empty($value)) {
							$value = $connector->getTransactionCount();
						}
						else {
							$value = intval($value);
						}

						break;
					case "balance.amount":
						$value = floatval(str_replace(',', '.', $value));
						break;
				}
			}
		);

		array_walk_recursive(
			$this->_transactions,
			function (&$value, $field) {
				switch ($field) {
					case 'title':
					case 'description':
						$value = trim($value);
						break;
					case 'credit':
					case 'debit':
						$value = floatval(str_replace(',', '.', $value));
						break;
					case 'date':
						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = $date->getTimeStamp();
						break;
				}
			}
		);
	}

	public function getLastTransactionDate() {
		return empty($this->_transactions) ? '' : $this->_transactions[0]['date'];
	}

	public function getFirstTransactionDate() {
		$last = end($this->_transactions);
		return empty($last) ? '' : $last['date'];
	}

	public function getTransactionCount() {
		return count($this->_transactions);
	}
}
?>