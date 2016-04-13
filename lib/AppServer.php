<?php

namespace Lib;
class AppServer{

	public function __construct(){

		echo __FILE__.':'."hello Appserver\n";
	}

	static function run(){

		echo '我跑起来了';
	}
}