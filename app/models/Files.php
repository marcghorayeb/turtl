<?php

namespace app\models;

class Files extends \app\models\AppBaseModel {
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'user_id' => array('type' => 'string'),
		'transaction_id' => array('type' => 'string'),
		
		'title' => array('type' => 'string')
	);

	protected $_meta = array(
		'source' => 'fs.files'
	);

	public function extension($entity) {
		return pathinfo($entity->filename, PATHINFO_EXTENSION);
	}
}
?>