<?php

class ClassFactory {

	public static function newInstance($className) {
		if( func_num_args() <= 1 ) {
			return new $className;
		} else {
			$args = func_get_args();
			array_shift($args);
			$reflection = new ReflectionClass($className);
			return $reflection->newInstanceArgs($args);
		}
	}


}
