<?php

namespace app\models;

use MongoDate;

class AppBaseModel extends \lithium\data\Model {
	/**
	 * Override to add a `created` and `modified` timestamp.
	 *
	 * @see lithium\data\Model::save()
	 */
	public function save($entity, $data = null, array $options = array()) {
		$data = empty($data) ? array() : $data;

		if (!$entity->exists()) {
			$data += array('created' => new MongoDate());
		}

		$data += array('modified' => new MongoDate());

		return parent::save($entity, $data, $options);
	}
}
?>
