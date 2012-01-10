<?php

namespace app\models;

use MongoId;
use MongoDate;
use DateTime;

use \lithium\util\String;
use \lithium\util\Validator;

use \app\models\Files;

class Transactions extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),

		'user_id' => array('type' => 'string'),
		'account_id' => array('type' => 'string'),
		'hash' => array('type' => 'string', 'default' => ''),
		
		'date' => array('type' => 'date', 'null' => false), // Date when the transaction was registered by the bank.
		'title' => array('type' => 'string', 'default' => ''),
		'description' => array('type' => 'string', 'default' => ''),
		'credit' => array('type' => 'float', 'default' => 0),
		'debit' => array('type' => 'float', 'default' => 0),
		'currency' => array('type' => 'string', 'default' => 'EUR'),
		
		'meta' => array('type' => 'object'),
		'meta.type' => array('type' => 'string', 'default' => ''), // Transaction type: CB, Cheque, etc...
		'meta.id' => array('type' => 'string', 'default' => ''), // Transaction id usually given by bank. Not usefull.
		'meta.date' => array('type' => 'date'), // Date of actual transaction.
		'meta.to' => array('type' => 'string', 'default' => ''), // Who is it destined to.
		'meta.category_id' => array('type' => 'string', 'default' => ''),
		'meta.tags' => array('type' => 'string', 'array' => true, 'default' => array()),
		'meta.note' => array('type' => 'string', 'default' => ''),
		'meta.verified' => array('type' => 'boolean', 'default' => false),
		'meta.file_id' => array('type' => 'string', 'array' => true, 'default' => array())
	);

	public $validates = array(
		'user_id' => 'notEmpty',
		'account_id' => 'notEmpty',
		'hash' => array(
			array('notEmpty'),
			array('uniqueHash')

		),

		'description' => 'notEmpty'
	);
	
	public static function config(array $options = array()) {
		parent::config($options);

		self::finder(
			'latest',
			array(
				'order' => array(
					'date' => 'DESC',
					'credit' => 'DESC',
					'debit' => 'ASC'
				)
			)
		);

		self::finder(
			'thisMonth',
			function ($self, $params, $chain) {
				$start = new MongoDate(strtotime('01-'.date('m-Y')));
				$end = new MongoDate(time());

				$params['options']['conditions']['date'] = array(
					'>=' => $start,
					'<=' => $end
				);

				$params['options']['order'] = array(
					'date' => 'DESC',
					'credit' => 'DESC',
					'debit' => 'DESC'
				);

				return $chain->next($self, $params, $chain);
			}
		);

		Validator::add('uniqueHash', function ($value, $format, $option) {
			if (strcmp($option['events'], 'update') === 0) {
				return true;
			}

			$exists = Transactions::first(array(
				'conditions' => array(
					'hash' => $value
				)
			));

			return empty($exists);
		});
	}
	
	/**
	 * Takes transaction data and turns them into meta data based on the bank type.
	 * Usually called once when inserting creating a new transaction.
	 * 
	 * Example:
	 * {{{
	 * $transactions = Transactions::create($data);
	 * }}}
	 * 
	 * @param object $entity
	 * @param string $bank Transaction's bank name. (SG, HSBC, ...)
	 * @return void
	 */
	public function populateDetails($entity, $bank) {
		$metaKeys = array('type', 'date', 'id', 'to');
		$templates = $bank->templates();
		
		foreach ($templates as $type => $expressions) {
			if (!is_array($expressions)) {
				$expressions = array($expressions);
			}

			foreach ($expressions as $regexp) {
				if (!empty($regexp)) {
					preg_match($regexp, $entity->description, $results);

					foreach ($metaKeys as $key) {
						if (!empty($results[$key])) {
							$entity->meta[$key] = $results[$key];
						}
					}
				}
			}
		}
	}

	/**
	 * Creates a hash for a new transaction. Automatically called on ::create() if `_id` is empty.
	 * 
	 * Example:
	 * {{{
	 * $transactions = Transactions::create($data);
	 * }}}
	 * 
	 * @param object $entity
	 * @return void
	 */
	public function createHash($entity) {
		if (empty($entity->hash)) {
			$amount = empty($entity->credit) ? $entity->debit : $entity->credit;
			$date = date('Y/m/d', $entity->date->sec);

			$str = $entity->user_id.$entity->account_id.$entity->title.$entity->description.$date.$amount;

			$entity->hash = String::hash($str, array('salt' => false));
		}
	}
	
	/**
	 * Sorts a list of transactions in descending historical order, and descending transaction amount.
	 * 
	 * Example:
	 * {{{
	 * $transactions = Transactions::all()->to('array');
	 * Transactions::sortTransactions($transactions);
	 * }}}
	 * 
	 * @param array $transactions: Transaction list.
	 * @return void
	 */
	public static function sortTransactions(array &$transactions) {
		usort(
			$transactions,
			function($a, $b) {
				$date1 = $a['date']; 
				$date2 = $b['date'];
				
				if ($date1 > $date2) {
					return -1;
				}
				else if ($date1 < $date2) {
					return 1;
				}
				else {
					$aa = empty($a['credit']) ? $a['debit'] : $a['credit'];
					$bb = empty($b['credit']) ? $b['debit'] : $b['credit'];
					if ($aa > $bb) {
						return -1;
					}
					else if ($aa < $bb){
						return 1;
					}
				}
				
				return 0;
			}
		);
	}

	/**
	 * Returns the number of unverified transactions for a given list of transactions.
	 * 
	 * @param array $transactions: Transaction list.
	 * @return int Number of unverified transactions.
	 */
	public static function countUnverified($transactions) {
		$count = 0;

		foreach ($transactions as $transaction) {
			if (empty($transaction->meta->verified)) {
				$count++;
			}
		}

		return $count;
	}

	public static function getMonthlyOccurences($transactions) {
	}

	public static function getBimonthlyOccurences($transactions) {
	}

	public static function getTrimestrialOccurences($transactions) {
	}

	public static function getSemestrialOccurences($transactions) {
	}

	/**
	 * Guess categories for a list of transactions. Optimized to atomically update the transactions.
	 * Will override the transaction's current category.
	 * 
	 * Example:
	 * {{{
	 * $transactions = Transactions::all();
	 * Transactions::guessCategories($transactions);
	 * }}}
	 * 
	 * @param array $transactions: Transaction list.
	 * @return void
	 */
	/*public static function guessCategories($transactions) {
		$updates = array();
		$removeCategory = array();
		$revenues = Categories::first(array('title' => 'Revenues'));

		foreach ($transactions as $transaction) {
			if ($transaction->credit > 0) {
				$updates[(string) $revenues->_id][] = (string) $transaction->_id;
			}
			else if (!empty($transaction->meta->to)) {
				$cat = Categories::guessCategory($transaction->meta->to);
				if (!empty($cat)) {
					$updates[$cat][] = (string) $transaction->_id;
				}
			}
			else {
				$removeCategory[] = (string) $transaction->_id;
			}
		}

		foreach ($updates as $category => $values) {
			self::setCategory($values, $category);
		}
	}*/

	/*public static function setCategory($transactions, $categoryId) {
		return self::update(
			array(
				'$set' => array('meta.category_id' => $categoryId)
			),
			array('_id' => $transactions)
		);
	}*/

	/**
	 * Guess category for the current transaction. Overwrites 'meta.cateogry_id' to the best possible category.
	 * 
	 * Example:
	 * {{{
	 * $transaction = Transactions::first($id);
	 * $transaction->guessCategory();
	 * $transaction->save();	
	 * }}}
	 * 
	 * @param entity $transaction.
	 * @return void
	 */
	public function guessCategory($entity) {
		$updateDictionary = true;
		$revenues = Categories::parents(array(
			'conditions' => array(
				'title' => 'Revenues'
			)
		));

		if ($entity->credit > 0) {
			$categoryId = (string) $revenues[0]->_id;
			$updateDictionary = false;
		}
		else {
			$categoryId = Categories::guessCategory($entity->meta->to);
		}

		$entity->applyCategory($categoryId, array('propagate' => false, 'updateDictionary' => $updateDictionary));
	}

	/**
	 * Changes a transaction's category and updates the corresponding dictionaries.
	 * @param entity $transaction.
	 * @param string $categoryId: new category id to be applied to the `$transaction`.
	 * @param array $options: Options when applying the category. By default, accepts:
	 * 		- `propagate`: Whether or not this category should be applied to all other similar transactions.
	 *		- `updateDictionary`: Wheter or not we shall update the transaction's dictionary as well.
	 */
	public function applyCategory($entity, $categoryId, $options = array()) {
		$defaults = array(
			'updateDictionary' => true,
			'propagate' => true
		);
		$options += $defaults;

		$revenues = Categories::parents(array(
			'conditions' => array(
				'title' => 'Revenues'
			),
			'limit' => 1
		));

		if (strcmp($categoryId, (string) $revenues[0]->_id) === 0) {
			$options['updateDictionary'] = false;
		}

		if ($entity->meta->category_id != $categoryId) {
			$entity->_decrementStats();
			$dictionary = $options['updateDictionary'] ? Dictionaries::createFromTransaction($entity) : null;

			if ($options['updateDictionary']) {
				$dictionary->decrementCategory($entity->meta->category_id);
			}

			$entity->meta->category_id = $categoryId;
			$entity->_incrementStats();

			if ($options['updateDictionary']) {
				$dictionary->incrementCategory($entity->meta->category_id);
				$dictionary->syncCategories();
				$dictionary->save();
			}

			if ($options['propagate']) {
				$entity->propagateCategory();
			}

			return true;
		}

		return false;
	}

	/**
	 * Propagate the current transaction's category to every other similar transactions.
	 * 
	 * @param entity $transaction.
	 * @param string $oldCategoryId: Used to update the previous category's dictionary.
	 * @return void
	 * @todo Atomically update the old category and chain the updates.
	 */
	public function propagateCategory($entity) {
		if (!empty($entity->meta->to)) {
			$transactions = self::all(array(
				'conditions' => array(
					'_id' => array('$ne' => (string) $entity->_id),
					'user_id' => $entity->user_id,
					'meta.to' => $entity->meta->to,
					'meta.category_id' => array('$ne' => $entity->meta->category_id)
				)
			));

			foreach ($transactions as $transaction) {
				$transaction->applyCategory($entity->meta->category_id, array('propagate' => false));
				$transaction->save();
			}

			return $transactions;
		}

		return array();
	}

	/**
	 * Extract hashtags from the user's note and stores them in meta.tags.
	 * 
	 * @param entity $entity.
	 */
	public function updateTags($entity) {
		preg_match_all('/#([\p{L}\p{Mn}]+)/u', $entity->meta->note, $tags, PREG_PATTERN_ORDER);
		$entity->meta->tags = $tags[1];
	}

	/**
	 * Creates a file in GridFS and associates it to $entity in $meta.file_id.
	 * 
	 * @param entity $entity.
	 * @param file $file
	 * @return string ObjectID of the created file.
	 */
	public function associateFile($entity, $file) {
		$file = Files::create($file);

		if ($file->save()) {
			$files = $entity->meta->file_id->to('array');
			$files[] = (string) $file->_id;
			$entity->meta->file_id = $files;

			return $files;
		}

		return 0;
	}

	/**
	 * Retreive a File object for a transaction's given file.
	 * 
	 * @param entity $transaction.
	 * @return file Assoficated file, 0 if none.
	 */
	public function getAssociatedFiles($entity) {
		return $entity->associatedFiles();
	}

	public function associatedFiles($entity) {
		if (!empty($entity->meta->file_id)) {
			$conditions = array(
				'_id' => $entity->meta->file_id->to('array'),
				'user_id' => $entity->user_id,
				'transaction_id' => (string) $entity->_id
			);

			return Files::all(compact('conditions'));
		}

		return null;
	}

	/**
	 * Returns a transaction's period in time.
	 * 
	 * @return string 'Year/Month' (ex: '2011/01').
	 */
	public function period($entity) {
		return date('Y/m', $entity->date->sec);
	}

	public function _decrementStats($entity) {
		if (empty($entity->meta->category_id)) {
			return true;
		}

		return Categories::update(
			array(
				'$inc' => array(
					'snapshots.$.activityCount' => -1,
					'snapshots.$.credit' => -$entity->credit,
					'snapshots.$.debit' => -$entity->debit
				)
			),
			array(
				'user_id' => $entity->user_id,
				'parent' => $entity->meta->category_id,
				'snapshots.period' => $entity->period()
			)
		);
	}

	/**
	 * @todo Atomic updates?
	 */
	public function _incrementStats($entity) {
		if (empty($entity->meta->category_id)) {
			return true;
		}

		$category = Categories::first(array(
			'conditions' => array(
				'user_id' => $entity->user_id,
				'parent' => $entity->meta->category_id
			)
		));

		if (empty($category)) {
			$category = Categories::create(array(
				'user_id' => $entity->user_id,
				'parent' => $entity->meta->category_id
			));
		}

		$snapshots = $category->snapshots->to('array');
		$exists = false;

		foreach ($snapshots as $i => $snapshot) {
			if (strcmp($snapshot['period'], $entity->period()) === 0) {
				$snapshots[$i]['activityCount']++;
				$snapshots[$i]['credit'] += $entity->credit;
				$snapshots[$i]['debit'] += $entity->debit;
				$exists = true;
			}
		}

		if (!$exists) {
			$snapshots[] = array(
				'activityCount' => 1,
				'credit' => $entity->credit,
				'debit' => $entity->debit,
				'period' => $entity->period()
			);
		}

		$category->snapshots = $snapshots;
		$category->save();
	}
}

/**
 * Filtered to create a transaction hash if it does not exist.
 */
Transactions::applyFilter('create', function ($self, $params, $chain) {	
	$doc = $chain->next($self, $params, $chain);

	if (empty($doc->_id)) {
		$doc->createHash();
	}
	
	return $doc;
});

/**
 * @todo clean tags in a more formal way, outside of the filter?
 */
Transactions::applyFilter('validates', function ($self, $params, $chain) {
	$entity = &$params['entity'];
	$tags = $entity['meta']['tags'];
	$newTags = array();

	foreach ($tags as $tag) {
		$tag = trim($tag);
		if (!empty($tag) && !in_array($tag, $newTags)) {
			$newTags[] = $tag;
		}
	}

	$entity->meta->tags = $newTags;

	if (($entity['debit'] != 0 && $entity['credit'] != 0) || ($entity['debit'] == 0 && $entity['credit'] == 0)) {
		return false;
	}

	return $chain->next($self, $params, $chain);
});
?>