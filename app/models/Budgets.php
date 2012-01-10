<?php

namespace app\models;

use DateTime;
use MongoDate;

use app\models\Categories;
use app\models\Transactions;

class Budgets extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'user_id' => array('type' => 'string'),
		'created' => array('type' => 'MongoDate'),
		'modified' => array('type' => 'MongoDate'),
		
		'title' => array('type' => 'string'),
		'description' => array('type' => 'string'),
		'period' => array('type' => 'array'),
		'period.start' => array('type' => 'MongoDate'),
		'period.end' => array('type' => 'MongoDate'),
		
		'categories' => array('type' => 'object', 'array' => true),
		'tags' => array('type' => 'object', 'array' => true),
		'snapshots' => array('type' => 'object', 'array' => true)
	);

	public $validates = array(
		'user_id' => 'notEmpty',
		'title' => 'notEmpty'
	);
	
	public function addCategory($entity, $category) {
	}
	
	public function updateCategory($entity, $category) {
	}
	
	public function removeCategory($entity, $category) {
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
		$conditions = array('user_id' => $entity['user_id']);
		$transactions = Transactions::thisMonth(compact('conditions'))->to('array');

		$snapshot = $entity->buildSnapshot($transactions);
		$entity->categories = $snapshot['categories'];
		$entity->tags = $snapshot['tags'];

		$entity->buildSnapshots();
	}

	/**
	 * Builds snapshots based on a per-month basis for each of the budget's categories.
	 * Stores the result in the $snapshots field.
	 * 
	 * @see app\models\Budgets::buildSnapshot
	 * @todo Optimize loop.
	 */
	public function buildSnapshots($entity) {
		$snapshots = array();
		$date = new DateTime();
		//$startOfMonth = DateTime::createFromFormat('d-m-Y', '01-'.date('m-Y'));
		$startOfMonth = new DateTime('first day');
		$conditions = array('user_id' => $entity['user_id']);
		$transactions = Transactions::latest(compact('conditions'))->to('array');

		foreach ($transactions as $transaction) {
			//$date = DateTime::createFromFormat('U', $transaction['date']->sec);
			$date->setTimeStamp($transaction['date']);

			if ($date < $startOfMonth) {
				$snapshots[$date->format('Y/m')]['transactions'][] = $transaction;
			}
		}

		foreach ($snapshots as $date => &$snapshotData) {
			$snapshotData = $entity->buildSnapshot($snapshotData['transactions']);
		}

		$entity->snapshots = $snapshots;
	}
	
	/**
	 * Builds a snapshot out of a list of transactions.
	 * A snapshot totals the debits, and transaction count (activityCount).
	 * 
	 * Example:
	 * {{{
	 * $transactions = Transactions::all();
	 * $snapshot = Accounts::buildSnapshot($transactions);
	 * }}}
	 *
	 * Usefull when building snapshots on a per-month basis.
	 * 
	 * @see app\models\Budgets::buildSnapshots
	 * @param array $transactions
	 * @return array Array containing the total of debit, credit, and activityCount.
	 */
	public function buildSnapshot($entity, $transactions) {
		$snapshot = array('categories' => array(), 'tags' => array());

		foreach ($entity->categories as $i => $category) {
			$snapshot['categories'][$i] = array(
				'limit' => $category['limit'],
				'category_id' => $category['category_id'],
				'category_title' => $category['category_title'],
				'monthSum' => 0,
				'activityCount' => 0
			);
		}

		foreach ($entity->tags as $i => $tag) {
			$snapshot['tags'][$i] = array(
				'tag_title' => $tag['tag_title'],
				'limit' => $tag['limit'],
				'monthSum' => 0,
				'activityCount' => 0
			);
		}

		foreach ($transactions as $transaction) {
			foreach ($snapshot['categories'] as &$category) {
				if (strcmp($transaction['meta']['category_id'], $category['category_id']) === 0) {
					$category['monthSum'] += $transaction['debit'] + $transaction['credit'];
					$category['activityCount']++;
					break;
				}
			}

			foreach ($snapshot['tags'] as &$tag) {
				if (in_array($tag['tag_title'], $transaction['meta']['tags'])) {
					$tag['monthSum'] += $transaction['debit'] + $transaction['credit'];
					$tag['activityCount']++;
				}
			}
		}

		return $snapshot;
	}

	/**
	 * Returns an average of how much a user spends on a certain category per month.
	 * This is based on the last three months of a user's expenses.
	 * 
	 * @param string $txt Word to be found.
	 * @return string Object ID of the best possible category.
	 */
	public static function suggestedAmount($category_id, $user_id) {
		$conditions = array('user_id' => $user_id, 'meta.category_id' => $category_id);
		$transactions = Transactions::latest(compact('conditions'))->to('array');
		$month = new DateTime();
		$monthSums = array();

		foreach ($transactions as $transaction) {
			$month->setTimeStamp($transaction['date']);
			
			if (!isset($monthSums[$month->format('n')])) {
				$monthSums[$month->format('n')] = 0;
			}

			$monthSums[$month->format('n')] -= $transaction['credit'] + $transaction['debit'];
		}

		return (count($monthSums) > 0 ) ? intval(array_sum($monthSums) / count($monthSums)) : 0;
	}

	public function guessSuggestedAmounts($entity) {
		foreach ($entity->categories as $category) {
			$category->suggestedAmount = self::suggestedAmount($category->category_id, $entity->user_id);
		}
	}

	public function cleanCategories($entity) {
		$categories = $entity->categories->to('array');

		foreach ($categories as $i => $category) {
			if (empty($category['limit'])) {
				unset($categories[$i]);
			}
		}

		$entity->categories = array_values($categories);
	}

	public function cleanTags($entity) {
		$tags = $entity->tags->to('array');

		foreach ($tags as $i => $tag) {
			if (empty($tag['limit'])) {
				unset($tag[$i]);
			}
		}

		$entity->tags = array_values($tags);
	}

	// ******************************************
	// Helper functions for graphic visualisations
	// ******************************************
	public function monthlyPosition($entity) {
		$currentMonth = $entity->categories->to('array');
		$categories = array();
		$tags = $entity->tags->to('array');

		foreach ($currentMonth as $i => $category) {
			if (!empty($category['category_title'])) {
				$categories[] = $category['category_title'];
			}
		}

		return compact('currentMonth', 'categories', 'tags');
	}

	public function history($entity) {
		$data = $entity->snapshots->to('array');
		return $data;
	}
	// ******************************************
}

Budgets::applyFilter('create', function ($self, $params, $chain) {
	$defaults = array(
		'title' => '',
		'description' => '',
		'period.start' => '',
		'period.end' => '',
		
		'categories' => array(),
		'tags' => array(),
		'snapshots' => array()
	);
	
	// Setup defaults for Budgets.
	$params['data'] = $params['data'] + $defaults;

	foreach ($params['data']['categories'] as &$category) {
		$category['limit'] = intval($category['limit']);
	}
	
	return $chain->next($self, $params, $chain);
});

Budgets::applyFilter('validates', function ($self, $params, $chain) {
	$entity = $params['entity'];
	$categories = &$entity->categories;

	foreach ($categories as $i => $category) {
		if (empty($category->category_id)) {
			return false;
		}

		// Take into account categories with a positive limit.
		if ($category->amount < 0) {
			return false;
		}
	}

	if (count($entity->categories) < 1) {
		return false;
	}

	return $chain->next($self, $params, $chain);
});
?>