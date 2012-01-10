<?php

namespace app\models;

use MongoDate;
use DateTime;

class Categories extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),
		
		'title' => array('type' => 'string', 'default' => ''),
		'dictionary' => array('type' => 'string', 'array' => true, 'default' => array()),

		'parent' => array('type' => 'string', 'default' => ''),

		'user_id' => array('type' => 'string' , 'default' => ''),
		'snapshots' => array('type' => 'object', 'array' => true, 'default' => array()),

		'notify' => array('type' => 'object'),
		'notify.summary' => array('type' => 'boolean', 'default' => false),
		'notify.limit' => array('type' => 'integer', 'default' => 0)
	);
	
	public $validates = array();
	
	public static function config(array $options = array()) {
		parent::config($options);

		self::finder(
			'parents',
			array(
				'conditions' => array(
					'parent' => ''
				),
				'order' => array(
					'title' => 'ASC'
				)
			)
		);
	}
	
	/**
	 * Erase all categories and start from fresh
	 * 
	 * @return void
	 */
	public static function initialise() {
		self::remove();
		
		$categories = array(
			'Journalier',
			'Education',
			'Famille',
			'Taxes',
			'Maison',
			'Santé',
			'Services',
			'Loisirs',
			'Sport',
			'Sorties',
			'Revenues',
			'Transport'
		);

		sort($categories);
		
		foreach ($categories as $cat) {
			$category = self::create(array('title' => $cat, 'parent' => ''));
			$category->buildDictionary();
			$category->save();
		}
	}

	/**
	 * Builds the dictionnary for a given category. Stores the result in $entity->dictionary.
	 * 
	 * @param entity $entity.
	 * @return void
	 */
	public function buildDictionary($entity) {
		$dictionary = array();
		$dictionaries = Dictionaries::all(
			array(
				'conditions' => array(
					'categories' => array(
						'$elemMatch' => array(
							'category_id' => (string) $entity->_id,
							'count' => array('$gte' => Dictionaries::$threshold)
						)
					)
				)
			)
		);

		foreach ($dictionaries as $dict) {
			if (!in_array($dict->title, $dictionary)) {
				$dictionary[] = $dict->title;
			}

			foreach ($dict->synonyms as $syn) {
				if (!in_array($syn, $dictionary)) {
					$dictionary[] = $syn;
				}
			}
		}

		sort($dictionary);

		$entity->dictionary = $dictionary;
	}

	/**
	 * Adds atomically a new word to an existing dictionary.
	 * 
	 * @param string $categoryId Object ID of the category to be updated.
	 * @param mixed $words Array of words or a string to be added to the category's dictionary.
	 * @return parser Parser
	 */
	public static function addToDictionary($categoryId, $words) {
		if (!empty($words)) {
			if (is_array($words)) {
				$words = array('$each' => $words);
			}

			return self::update(
				array(
					'$addToSet' => array(
						'dictionary' => $words
					)
				),
				array(
					'$or' => array(
						array('_id' => $categoryId)
					)
				)
			);
		}
	}

	/**
	 * Removes atomically a word from an existing dictionary.
	 * 
	 * @param string $categoryId Object ID of the category to be updated.
	 * @param mixed $words Array of words or a string to be added to the category's dictionary.
	 * @return parser Parser
	 */
	public static function removeFromDictionary($categoryId, $words) {
		if (!empty($words)) {
			if (!is_array($words)) {
				$words = array($words);
			}

			return self::update(
				array(
					'$pullAll' => array(
						'dictionary' => $words
					)
				),
				array(
					'$or' => array(
						array('_id' => $categoryId)
					)
				)
			);
		}
	}

	/**
	 * Returns the best possible category for a given dictionary word.
	 * 
	 * @param string $txt Word to be found.
	 * @return string Object ID of the best possible category.
	 */
	public static function guessCategory($txt) {
		if (!empty($txt)) {
			$categories = Categories::parents(array(
				'conditions' => array(
					'dictionary' => $txt
				)
			));

			if (count($categories) > 0) {
				return (string) $categories[0]->_id;
			}
		}

		return '';
	}

	public function title($entity) {
		if (!empty($entity->parent)) {
			return self::first($entity->parent)->title;
		}

		return $entity->title;
	}

	public static function setSummary($categoryId, $userId, $notify) {
		return self::update(
			array(
				'$set' => array(
					'notify.summary' => $notify
				)
			),
			array(
				'_id' => $categoryId,
				'user_id' => $userId
			)

		);
	}

	public static function setLimit($categoryId, $userId, $limit) {
		return self::update(
			array(
				'$set' => array(
					'notify.limit' => $limit
				)
			),
			array(
				'_id' => $categoryId,
				'user_id' => $userId
			)
		);
	}
}
?>