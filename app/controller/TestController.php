<?php

/**
 * Class title
 *
 * Class Description
 *
 * @author Yugo Kimura <me@example.com>
 */

class TestController extends Controller {

	function __construct() {
		parent::__construct();
	}

	function __destruct() {
		parent::__destruct();
	}

	function view($arg) {
		print_r($arg);
		$this->renderView($arg['view'] . '.html');
	}

        /**
         * TITLE
         *
         * DESCRIPTION
         *
         *
         * @param type text
         * pparam type text
         * @return  type test
         */
	function index($arg){
		//print_r($arg);
		phpinfo();
		//$this->renderView('test.html');
	}

	/**
	 * TITLE
	 *
	 * DESCRIPTION
	 * 
	 *
	 * @param type text
	 * pparam type text
	 * @return  type test
	 */	
	function get() {

	}

        /**
         * TITLE
         *
         * DESCRIPTION
         *
         *
         * @param type text
         * pparam type text
         * @return  type test
	 */
	function post() {

	}

        /**
         * TITLE
         *
         * DESCRIPTION
         *
         *
         * @param type text
         * pparam type text
         * @return  type test
         */
	function delete() {

	}

        /**
         * TITLE
         *
         * DESCRIPTION
         *
         *
         * @param type text
         * pparam type text
         * @return  type test
         */
	function patch() {

	}
}
