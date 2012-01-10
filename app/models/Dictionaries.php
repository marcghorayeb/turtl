<?php

namespace app\models;

use MongoDate;
use DateTime;

class Dictionaries extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),

		'title' => array('type' => 'string', 'default' => ''),
		'synonyms' => array('type' => 'string', 'array' => true, 'default' => array()),

		'categories' => array('type' => 'object', 'array' => true),
		'categories.category_id' => array('type' => 'string', 'default' => ''),
		'categories.count' => array('type' => 'integer', 'default' => 0)
	);
	
	public $validates = array(
		'title' => 'notEmpty'
	);

	public static $threshold = 1;

	public static function createFromTransaction($transaction) {
		$dictionary = null;

		if (!empty($transaction->meta->to)) {
			$dictionary = self::first(array(
				'conditions' => array(
					'$or' => array(
						array('title' => $transaction->meta->to),
						array('synonyms' => $transaction->meta->to)
					)
				)
			));

			if (empty($dictionary)) {
				$dictionary = self::create(array('title' => $transaction->meta->to));
				$dictionary->save();
			}
		}

		return $dictionary;
	}

	public function incrementCategory($entity, $categoryId, $step = 1) {
		if (!empty($categoryId)) {
			$categories = $entity->categories->to('array');
			$new = true;

			foreach ($categories as $i => $c) {
				if (strcmp($c['category_id'], $categoryId) === 0) {
					$categories[$i]['count'] += $step;
					$new = false;
					break;
				}
			}

			if ($new) {
				$categories[] = array(
					'category_id' => $categoryId,
					'count' => $step
				);
			}

			$entity->categories = $categories;
		}
	}

	public function decrementCategory($entity, $categoryId, $step = 1) {
		if (!empty($categoryId)) {
			$categories = $entity->categories->to('array');

			foreach ($categories as $i => $c) {
				if (strcmp($c['category_id'], $categoryId) === 0) {
					$categories[$i]['count'] -= $step;
					
					if ($c['count'] < 0) {
						$categories[$i]['count'] = 0;
					}

					break;
				}
			}

			$entity->categories = $categories;
		}
	}

	public function syncCategories($entity) {
		$categories = $entity->categories->to('array');
		$bestCount = 0;
		$categoryId = '';
		$words = array_values(array_unique($entity->synonyms->to('array') + array($entity->title)));

		foreach ($categories as $category) {
			Categories::removeFromDictionary($category['category_id'], $words);

			if ($category['count'] > $bestCount) {
				$bestCount = $category['count'];
				$categoryId = $category['category_id'];
			} 
		}
		
		if (!empty($categoryId) && $bestCount >= self::$threshold) {
			Categories::addToDictionary($categoryId, $words);
		}
	}
}
?>