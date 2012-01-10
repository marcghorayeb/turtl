<?php

namespace app\models;

/**
 * EmbeddedModel class.
 */
class EmbeddedModel extends \lithium\data\Model {
	protected $_meta = array (
		'source' => false,
		'connection' => false,
		'key' => '',
		'locked' => false
	);
}
?>