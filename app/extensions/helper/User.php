<?php

namespace app\extensions\helper;

class User extends \lithium\template\Helper {
	public function tagTitle($tag) {
		return '#'.$tag;
	}
}

?>