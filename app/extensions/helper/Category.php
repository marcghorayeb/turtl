<?php

namespace app\extensions\helper;

class Category extends \lithium\template\Helper {
	public function title($categories, $id) {
		foreach ($categories as $category) {
			if (strcmp($category['_id'], $id) === 0) {
				return $category['title'];
			}
		}
		return '';
	}
}
?>