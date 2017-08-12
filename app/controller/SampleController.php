<?php

/**
 * Class title
 *
 * Class Description
 *
 * @author Yugo Kimura <me@example.com>
 * @since 1.0.0
 */

class SampleController extends Controller {

	function __construct() {
		parent::__construct();
	}

	function __destruct() {
		parent::__destruct();
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
		print_r($arg);
		$this->renderView('test.html');
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
