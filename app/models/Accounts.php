<?php

namespace app\models;

use DateTime;
use MongoDate;

use lithium\security\Auth;

use app\models\Transactions;
use app\models\Users;
use app\models\Banks;

use app\extensions\data\parser\CSV;

class Accounts extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),

		'user_id' => array('type' => 'string', 'default' => ''),
		'bank_id' => array('type' => 'string', 'default' => ''),

		'active' => array('type' => 'boolean', 'default' => true),

		'defaultCurrency' => array('type' => 'string', 'default' => 'EUR'),
		'id' => array('type' => 'string', 'default' => ''),
		'title' => array('type' => 'string', 'default' => ''),
		'bankCode' => array('type' => 'string', 'default' => ''),
		'agencyCode' => array('type' => 'string', 'default' => ''),

		'period' => array('type' => 'object'),
		'period.start' => array('type' => 'date'),
		'period.end' => array('type' => 'date'),
		'period.activityCount' => array('type' => 'integer', 'default' => 0),

		'balance' => array('type' => 'object'),
		'balance.date' => array('type' => 'date'),
		'balance.amount' => array('type' => 'float', 'default' => 0),

		'portal' => array('type' => 'object'),
		'portal.login' => array('type' => 'string', 'default' => ''),
		'portal.password' => array('type' => 'string', 'default' => ''),
		'portal.lastAccess' => array('type' => 'date'),

		'snapshots' => array('type' => 'object', 'array' => true, 'default' => array())
	);

	public $validates = array(
		'id' => 'notEmpty',
		'user_id' => 'notEmpty',
		'bank_id' => 'notEmpty'
	);

	protected static $_aes = "AES Key";

	protected static $_connectors = array(
		'SG' => 'app\extensions\data\connector\SG'
	);

	protected $_connector = null;

	/**
	 * Creates and returns a data parser for a given $filepath and $bank.
	 *
	 * @see app\extensions\data\Parser
	 * @param string $filepath Path to the bank file to be parsed.
	 * @param string $bank Bank name which determines the file format for the parser.
	 * @return parser Parser
	 * @todo Switch based on the file extension.
	 */
	/*public static function createParser($filepath, $bank, $guessBank) {
		$parser = new CSV(array(
			'bank' => $bank,
			'file' => $filepath,
			'guessBank' => $guessBank,
			'readFile' => true
		));

		return $parser;
	}*/

	/**
	 * Creates and returns a website scraper for a given $bank, $login and $password.
	 * @see app\extensions\data\Connector
	 * @param string $bank Bank name which determines the file format for the parser.
	 * @param string $login The login used to identify a user on the bank's website.
	 * @param string $password The password used by the user on the bank's website.
	 * @return connector Connector
	 */
	public static function createConnector($bank, $login, $password) {
		if (!empty(self::$_connectors[$bank])) {
			$config = compact('login', 'password');
			$connector = self::$_connectors[$bank];
			return new $connector($config);
		}

		return null;
	}

	/**
	 * Creates and sets a website scraper for a given $entity.
	 * @see app\extensions\data\Connector
	 * @see app\models\Accounts::createConnector()
	 * @param account $entity
	 * @return entity Account entity so you can chain commands.
	 */
	public function makeConnector($entity) {
		$bank = Banks::first($entity->bank_id);

		if (empty($this->_connector) && !empty($bank)) {
			$this->_connector = self::createConnector(
				$bank->short_title,
				$entity->portal->login,
				self::_decrypt($entity->portal->password)
			);
		}

		return $entity;
	}

	/**
	 * Start scraping the portal website.
	 * @see app\extensions\data\Connector
	 * @param entity $entity.
	 * @return boolean True if the scrape was executed, false otherwise.
	 */
	public function scrape($entity) {
		if (!empty($this->_connector)) {
			$lastAccess = $entity->portal->lastAccess->sec;
			$now = time();

			// Make sure you didn't already scrape in the last hour. Let's not spam the website.
			//if (empty($lastAccess) || ($now - $lastAccess) > 3600) {
				$entity->portal->lastAccess = $now;
				$this->_connector->scrape($entity->id);
				return true;
			//}
		}

		return false;
	}

	/**
	 * Start scraping the portal website and returns the transactions for a given $entity.
	 * @see app\extensions\data\Connector
	 * @param entity $entity.
	 * @return array Transactions data.
	 */
	public function fetchTransactions($entity) {
		if ($entity->makeConnector()->scrape()) {
			return $this->_connector->getTransactions();
		}

		return array();
	}

	/**
	 * Inputs transactions into the database for a given account.
	 *
	 * @param object $entity
	 * @param array $transactions List of transactions to be inserted in the database.
	 */
	public function inputTransactions($entity, array $transactions) {
		$user_id = $entity->user_id;
		$account_id = (string) $entity->_id;
		$bank = Banks::first($entity->bank_id);
		$keys = compact('user_id', 'account_id');

		foreach ($transactions as $transaction) {
			$data = $keys + $transaction;
			$t = Transactions::create($data);

			if ($t->validates()) {
				$t->populateDetails($bank);
				$t->guessCategory();
				$t->save(null, array('validate' => false));
			}
		}
	}

	/**
	 * Merges account info from $source into $destination.
	 *
	 * Example:
	 * {{{
	 * $existingAccount = Account::first($id);
	 * $newAccount = Account::create($newData);
	 * $existingAccount->mergeHeaders($newAccount)
	 * $existingAccount->save();
	 * }}}
	 *
	 * @see app\models\Accounts::buildSnapshots
	 * @param object $entity Existing account.
	 * @param object $source New account from which data will be copied into $entity.
	 */
	public function mergeHeaders($destination, $source) {
		$date1 = $destination->balance->date->sec;
		$date2 = $source->balance->date->sec;

		if ($date1 < $date2) {
			$destination['balance']['date'] = $source['balance']['date'];
			$destination['balance']['amount'] = $source['balance']['amount'];
		}

		$date1 = $destination->period->start;
		$date2 = $source->period->start;

		if ($date1 > $date2) {
			$destination->period->start = $source->period->start;
		}

		$date1 = $destination->period->end;
		$date2 = $source->period->end;

		if ($date1 < $date2) {
			$destination->period->end = $source->period->end;
		}
	}

	/**
	 * Returns a list of MongoIds for a given list of Accounts.
	 *
	 * @param array $accounts
	 */
	public static function getIds($accounts) {
		$ids = array();

		foreach ($accounts as $account) {
			$ids[] = (string) $account->_id;
		}

		return $ids;
	}

	/**
	 * Builds snapshots based on a per-month basis for a list of given transactions.
	 * Stores the result in `$entity->snapshots`.
	 *
	 * @see app\models\Accounts::buildSnapshots
	 * @param object $entity
	 * @todo Optimize loop.
	 */
	public function refresh($entity) {
		$lastCounted = $entity->balance->date->sec;
		$lastBalance = $entity->balance->amount;

		$transactions = $entity->transactions(0)->to('array');

		foreach ($transactions as $transaction) {
			$date = $transaction['date'];

			if ($date > $lastCounted) {
				$lastBalance += $transaction['credit'] + $transaction['debit'];
			}
		}

		$entity->balance->amount = $lastBalance;
		$entity->balance->date = $transactions[0]['date'];

		$entity->period->end = $transactions[0]['date'];
		$entity->period->start = $transactions[count($transactions)-1]['date'];
		$entity->period->activityCount = count($transactions);

		$entity->buildSnapshots($transactions);
	}

	/**
	 * Builds snapshots based on a per-month basis for a list of given transactions.
	 * Stores the result in the $snapshots field.
	 *
	 * @see app\models\Accounts::buildSnapshot
	 * @todo Optimize loop.
	 */
	public function buildSnapshots($entity, $transactions = array()) {
		$snapshots = array();
		$startOfMonth = strtotime('first day');

		if (empty($transactions)) {
			$transactions = $entity->transactions(0)->to('array');
		}

		foreach ($transactions as $transaction) {
			$date = $transaction['date'];

			if ($date < $startOfMonth) {
				$snapshots[date('Y/m', $date)]['transactions'][] = &$transaction;
			}
		}

		foreach ($snapshots as &$snapshotData) {
			$snapshotData = self::buildSnapshot($snapshotData['transactions']);
		}

		$entity->snapshots = $snapshots;
	}

	/**
	 * Builds a snapshot out of a list of transactions.
	 * A snapshot totals the debits, credits, and transaction count (activityCount).
	 *
	 * Example:
	 * {{{
	 * $transactions = Transactions::all();
	 * $snapshot = Accounts::buildSnapshot($transactions);
	 * }}}
	 *
	 * Usefull when building snapshots on a per-month basis.
	 *
	 * @see app\models\Accounts::buildSnapshots
	 * @param array $transactions
	 * @return array Array containing the total of debit, credit, and activityCount.
	 */
	public static function buildSnapshot($transactions) {
		$snapshot = array('debit' => 0, 'credit' => 0, 'activityCount' => 0);

		foreach ($transactions as $transaction) {
			$snapshot['debit'] += (!empty($transaction['debit'])) ? $transaction['debit'] : 0;
			$snapshot['credit'] += (!empty($transaction['credit'])) ? $transaction['credit'] : 0;
			$snapshot['activityCount'] += 1;
		}

		return $snapshot;
	}

	// ******************************************
	// Bridges
	// ******************************************
	public function transactions($entity, $limit = 30) {
		$conditions = array('user_id' => $entity->user_id, 'account_id' => (string) $entity->_id);
		return Transactions::latest(compact('conditions', 'limit'));
	}
	// ******************************************

	// ******************************************
	// Helpers
	// ******************************************

	/**
	 * Returns a list of periods available for a list of accounts.
	 * A period is usually a month designated by the format `mm-yyyy`.
	 *
	 * @param array $accounts List of accounts.
	 * @return array Periods for the accounts given.
	 */
	public static function periods($accounts) {
		$periods = array();
		$start = null;
		$end = null;

		if (count($accounts) < 1) {
			return $periods;
		}

		foreach ($accounts as $account) {
			if (empty($start) || $account->period->start < $start) {
				$start = $account->period->start;
			}

			if (empty($end) || $account->period->end > $end) {
				$end = $account->period->end;
			}
		}

		$start = $start->sec;
		$end = $end->sec;

		$period = $start;
		while ($period < $end) {
			$periods[] = date('Y/m', $period);
			$period = strtotime('next month', $period);
		}
		$periods[] = date('Y/m', $end);

		return array_unique($periods);
	}

	public static function _encrypt($string) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::$_aes, $string, MCRYPT_MODE_ECB, $iv)));
	}

	public static function _decrypt($string) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::$_aes, base64_decode($string), MCRYPT_MODE_ECB, $iv));
	}
	// ******************************************
}
?>