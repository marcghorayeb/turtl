<?php

namespace app\models;

use MongoDate;
use DateTime;

use lithium\util\Validator;

class Banks extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'created' => array('type' => 'date'),
		'modified' => array('type' => 'date'),

		'title' => array('type' => 'string'),
		'short_title' => array('type' => 'string'),

		'accounts' => array('type' => 'string', 'array' => true, 'default' => array()),

		'templates' => array('type' => 'object'),
		'templates.carte' => array('type' => 'string', 'array' => true, 'default' => array()),
		'templates.prelevement' => array('type' => 'string', 'array' => true, 'default' => array()),
		'templates.cheque' => array('type' => 'string', 'array' => true, 'default' => array()),
		'templates.virement' => array('type' => 'string', 'array' => true, 'default' => array())
	);

	public $validates = array(
		'title' => 'notEmpty',
		'short_title' => 'notEmpty',
	);

	/**
	 * Erase all banks and start from fresh
	 *
	 * @return void
	 */
	public static function initialise() {
		self::remove();

		$banks = array(
			array(
				'title' => 'Société Générale',
				'short_title' => 'SG',
				'accounts' => array('/Compte Bancaire/'),
				'templates' => array(
					'carte' => array('/^(?<type>CARTE) (?<bankAccount>[X][0-9]{4}) (?<date>[0-9]{2}[\/][0-9]{2}) (?<to>.*)$/'),
					'prelevement' => array(),
					'cheque' => array('/^REMISE (?<type>CHEQUE) (?<id>[0-9]{7} [0-9]{3}) DE [0-9]+ CHQ$/', '/^(?<type>CHEQUE) (?<id>[0-9]*)$/'),
					'virement' => array()
				)
			)
		);

		foreach ($banks as $b) {
			$bank = self::create($b);
			$bank->save();
		}
	}

	public function templates($entity) {
		$templates = $entity->templates->to('array');

		foreach ($templates as $i => $template) {
			if (empty($template)) {
				unset($templates[$i]);
			}
		}

		return $templates;
	}

	public function filterAccountList($entity, &$accounts) {
		$templates = $entity->accounts->to('array');

		foreach ($accounts as $i => $account) {
			$erase = true;

			if (!empty($account['title'])) {
				foreach ($templates as $regexp) {
					preg_match($regexp, $account['title'], $results);

					if (!empty($results))
						$erase = false;
						break;
				}
			}

			if ($erase) {
				unset($accounts[$i]);
			}
		}
	}

	public function cleanTemplates($entity) {
		$templates = $entity->templates->to('array') + array('carte' => array(), 'prelevement' => array(), 'cheque' => array(), 'virement' => array());

		foreach ($templates as &$t) {
			foreach ($t as $j => $template) {
				if (empty($template)) {
					unset($t[$j]);
				}
			}
		}

		$entity->templates = $templates;
	}
}

/*Banks::applyFilter('create', function($self, $params, $chain) {
	$defaults = array(
	);

	$params['data'] = $params['data'] + $defaults;

	return $chain->next($self, $params, $chain);
});*/

Banks::applyFilter('validate', function ($self, $params, $chain) {
	$params['entity']->cleanTemplates();

	return $chain->next($self, $params, $chain);
});
?>