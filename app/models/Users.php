<?php

namespace app\models;

use MongoDate;
use Exception;

use lithium\data\collection\DocumentArray;
use lithium\data\entity\Document;
use lithium\security\Auth;
use lithium\security\Password;
use lithium\util\String;

use app\models\Transactions;
use app\models\Accounts;
use app\models\Budgets;

class Users extends \app\models\AppBaseModel {
	public $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),

		'email' => array('type' => 'string'),
		'password' => array('type' => 'password'),
		'activated' => array('type' => 'boolean', 'default' => false),
		
		'firstName' => array('type' => 'string'),
		'familyName' => array('type' => 'string'),
		
		'login' => array('type' => 'object'),
		'login.count' => array('type' => 'integer', 'default' => 0),
		'login.failed' => array('type' => 'integer', 'default' => 0),
		'login.last' => array('type' => 'date', 'default' => ''),
		'login.notify' => array('type' => 'boolean', 'default' => false),
		
		'tags' => array('type' => 'string', 'array' => true, 'default' => array())
	);
	
	public $validates = array(
		'email' => array(
			array('notEmpty', 'message' => 'Veuillez fournir une adresse de courrier électronique.'),
			array('email', 'message' => 'L\'adresse de courrier électronique n\'est pas valide.')
		),
		'password' => array(
			array('notEmpty', 'message' => 'Veuillez fournir un mot de passe'),
			array('lengthBetween', 'min' => '6', 'message' => 'Veuillez fournir un mot de passe long de 6 caractères au moins.')
		),
		'firstName' => array(
			array('notEmpty', 'message' => 'Veuillez fournir un prénom.')
		),
		'familyName' => array(
			array('notEmpty', 'message' => 'Veuillez fournir un nom de famille.')
		)
	);
	
	public static function successfulLogin($id) {
		return self::update(
			array(
				'$set' => array(
					'login.last' => new MongoDate(),
					'login.failed' => 0
				),
				'$inc' => array(
					'login.count' => 1
				)
			),
			array(
				'_id' => $id
			)
		);
	}

	public static function failedLogin($email) {
		return self::update(
			array(
				'$inc' => array(
					'login.failed' => 1
				)
			),
			array(
				'email' => $email
			)
		);
	}

	/**
	 * Returns a registration code so that a user can activate his account.
	 * If a hash is given, the function returns true or false whether the codes match.
	 * 
	 * @param entity $user.
	 * @param string $compare: Optional, user given code for comparison.
	 * @return mixed A string representing the registration code for the user, or a boolean comparison.
	 */
	public function registrationHash($entity, $compare = '') {
		$str = $entity->firstName
			.$entity->familyName
			.$entity->created->sec
			.strtotime('last second of', $entity->created->sec);
		
		$hash = String::hash($str, array('salt', false));

		if (!empty($compare)) {
			return (strcmp($str, $compare) === 0) ? true : false;
		}
		
		return $hash;
	}

	/**
	 * Returns the full name of the user.
	 * 
	 * @param entity $user.
	 * @return The user's first name, followed by the user's second name.
	 */
	public function fullName($entity) {
		return $entity->firstName.' '.$entity->familyName;
	}

	// ******************************************
	// Tags
	// ******************************************
	/**
	 * Returns the user's tags.
	 * 
	 * @param entity $user.
	 * @param boolean $join: Optional. If true, the return value will be a string of comma separated tags.
	 * @return User's tags in an array format ($join = false) or string format ($join = true).
	 */
	public function tags($entity, $join = false) {
		if ($join) {
			return implode(', ', $entity->tags);
		}

		return $entity->tags;
	}

	public function refreshTags($entity) {
		$transactions = Transactions::all(
			array(
				'conditions' => array(
					'user_id' => (string) $entity->_id,
					'meta.tags' => array('$ne' => '')

				),
				'fields' => array(
					'meta.tags'
				)
			)
		);

		$tags = array();
		foreach ($transactions as $transaction) {
			foreach ($transaction->meta->tags as $tag) {
				if (!in_array($tag, $tags)) {
					$tags[] = $tag;
				}
			}
		}

		sort($tags);

		$entity->tags = $tags;
		$entity->save();
	}

	public function hasTag($entity, $tag) {
		return in_array($tag, $entity->tags, true);
	}
	
	public function addTags($entity, array $tags) {
		$entity->tags += array_diff($entity->tags, $tags);
	}
	// ******************************************

	// ******************************************
	// Bridges
	// ******************************************
	/**
	 * Returns the user's accounts.
	 * 
	 * @param entity $user.
	 * @return Accounts array.
	 */
	public function accounts($entity) {
		return Accounts::all(array(
				'conditions' => array(
					'user_id' => (string) $entity->_id
				)
		));
	}

	/**
	 * Returns the user's account for a given account id.
	 * Makes sure the user has the ownership of the account.
	 * 
	 * @param entity $user.
	 * @param string $id: Account ID to return.
	 * @return Account entity.
	 */
	public function account($entity, $id) {
		return Accounts::first(array(
			'conditions' => array(
				'_id' => $id,
				'user_id' => (string) $entity->_id
			)
		));
	}

	/**
	 * Returns a given transaction, makes sure the user has the ownership.
	 * 
	 * @param entity $user.
	 * @param string $id: transaction id to return.
	 * @return Transaction entity.
	 */
	public function transaction($entity, $id) {
		return Transactions::first(array(
			'conditions' => array(
				'_id' => $id,
				'user_id' => (string) $entity->_id
			)
		));
	}

	/**
	 * Returns a given number of transactions for the user, sorted by date, debit, and credit.
	 * 
	 * @param entity $user.
	 * @param string $limit: Optional, will limit the number of results returned.
	 * @return Transactions array.
	 */
	public function transactions($entity, $limit = null) {
		$limit = empty($limit) ? 0 : $limit;
		$conditions = array('user_id' => (string) $entity->_id);
		return Transactions::latest(compact('conditions', 'limit'));
	}

	/**
	 * Returns a list of transactions for a given list of transaction ids.
	 * 
	 * @param entity $user.
	 * @param array $id: list of transaction IDs.
	 * @return Transactions array.
	 */
	public function transactionsById($entity, array $id = array()) {
		$conditions = array('_id' => $id, 'user_id' => (string) $entity->_id);
		return Transactions::latest(compact('conditions'));
	}

	/**
	 * Returns the user's transactions for the current month.
	 * 
	 * @param entity $user.
	 * @return Transactions array.
	 */
	public function transactionsForThisMonth($entity) {
		$conditions = array('user_id' => (string) $entity->_id);
		return Transactions::thisMonth(compact('conditions'));
	}

	/**
	 * Returns the user's transaction for a given period.
	 * 
	 * @param entity $user.
	 * @param int $start: epoch number of the period start.
	 * @param int $end: epoch number of the period end.
	 * @return Transactions array.
	 */
	public function transactionsForPeriod($entity, $start, $end) {
		$conditions = array(
			'user_id' => (string) $entity->_id,
			'date' => array()
		);

		if (!empty($start)) {
			$conditions['date'] += array('>=' => new MongoDate($start));
		}

		if (!empty($end)) {
			$conditions['date'] += array('<=' => new MongoDate($end));
		}

		if (empty($conditions['date'])) {
			return array();
		}

		return Transactions::latest(compact('conditions'));
	}

	/**
	 * Returns a file for a given id. Makes sure the user has ownership.
	 * 
	 * @param entity $user.
	 * @param string $id: File id to return.
	 * @return File entity.
	 */
	public function file($entity, $id) {
		return Files::first(array(
			'conditions' => array(
				'_id' => $id,
				'user_id' => (string) $entity->_id
			)
		));
	}

	/*public function getBudget($entity) {
		return Budgets::first(array(
			'conditions' => array(
				'user_id' => (string) $entity->_id
			)
		));
	}*/

	public function deleteAccount($entity, $account_id) {
		$account = $entity->account($account_id);
		$transactions = $account->transactions(0);

		foreach ($transactions as $transaction) {
			$transaction->applyCategory('', array('propagate' => false, 'updateDictionary' => false));
			$transaction->delete();
		}

		$account->delete();

		$entity->refreshTags();
	}

	/*public function deleteBudget($entity, $budget_id) {
		Budgets::remove(array(
			'_id' => $budget_id,
			'user_id' => (string) $entity->_id
		));
	}*/

	/**
	 * Returns the user's categories.
	 * 
	 * @param entity $user.
	 * @return Categories array.
	 */
	public function categories($entity) {
		return Categories::all(array(
			'conditions' => array(
				'user_id' => (string) $entity->_id
			)
		));
	}

	/**
	 * Returns a category for a given id. Makes sur the user has ownership.
	 * 
	 * @param entity $user.
	 * @param string $id: ID of the category to return.
	 * @return Category entity.
	 */
	public function category($entity, $id) {
		return Categories::all(array(
			'conditions' => array(
				'_id' => $id,
				'user_id' => (string) $entity->_id
			)
		));
	}
	// ******************************************

	// ******************************************
	// Weekly summary
	// ******************************************

	/**
	 * @todo
	 */
	public function weeklySummary($entity) {
		return array();
	}
	// ******************************************
}

/**
 * Filter in place to validate password and passwordVerify upon registering.
 */
Users::applyFilter('validates', function($self, $params, $chain) {
	$result = $chain->next($self, $params, $chain);
	
	if ($result) {
		$document = &$params['entity'];
		
		if (!$document->_id) {
			if (!empty($document->passwordVerify) && strcmp($document->password, $document->passwordVerify) === 0) {
				$document->password = Password::hash($document->password);
				unset($document->passwordVerify);
			}
			else {
				$result = false;
			}
		}
	}
	
	return $result;
});

/**
 * Filter in place to update the current session user info if a user was successfully updated.
 */
Users::applyFilter('save', function($self, $params, $chain) {
    $result = $chain->next($self, $params, $chain);
	
	if ($result) {
		$document = &$params['entity'];
		$currentUser = Auth::check('user');
		
		if (!empty($document->_id) && $currentUser['_id'] == (string) $document->_id) {
			Auth::set('user', $document->data());
		}
	}
	
	return $result;
});
?>