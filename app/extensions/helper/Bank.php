<?php

namespace app\extensions\helper;

class Bank extends \lithium\template\Helper {
	public function title($account) {
		switch ($account['bankName']) {
			case 'SG':
				return 'Société Générale';
			case 'HSBC':
			default:
				return $account['bankName'];
		}
	}

	public function credit($transaction) {
		if (empty($transaction->credit)) {
			return '';
		}

		$txt = $transaction->credit;

		switch ($transaction->currency) {
			case 'EUR':
			default:
				return $txt.'€';
		}
	}

	public function debit($transaction) {
		if (empty($transaction->debit)) {
			return '';
		}

		$txt = $transaction->debit;

		switch ($transaction->currency) {
			case 'EUR':
			default:
				return $txt.'€';
		}
	}

	public function currency($transaction) {
		if (empty($transaction->credit)) {
			return $this->debit($transaction);
		}

		return $this->credit($transaction);
	}
}

?>