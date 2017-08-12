<?php

class Controller {

	protected $params;
	protected $redis;
	protected $validation_result;
	function __construct() {
		$this->params = [];
		$this->redis = redis_factory();
	}

	function __destruct() {
	}

	function assign($key, $value) {
		$this->params[$key] = $value;
	}

	function renderView($template) {
		$this->params['template'] = $template;
		extract($this->params);
		include(DIR_VIEW . '/layout.html');
	}

	function renderJson( $json ) {
		header('Content-type:application/json; charset=utf-8');
		print json_encode( $json );
	}


	function input($key, $default = "") {
		if( isset($_REQUEST[$key]) )
			return $_REQUEST[$key];
		else
			return $default;
	}

	function validate($params = array()) {

		$this->validation = true;
		if( isset($_REQUEST['body']) ) {
			// 連想配列として展開
			$body = json_decode($_REQUEST['body'], true);
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
				break;
				case JSON_ERROR_DEPTH:
				case JSON_ERROR_STATE_MISMATCH:
				case JSON_ERROR_CTRL_CHAR:
				case JSON_ERROR_SYNTAX:
				case JSON_ERROR_UTF8:
				default:
					$this->validation = false;
					return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
			}
			$_REQUST = array_merge($_REQUEST, $body);
		}

		foreach($params as $key => $rule_string) {
		
$k = strtok($key, '.');
/**
$current = $b;
while($p != false) {

        if(!isset($current[$p])) {
                print $p . " false";
                break;
        }
        // キー名の値を代入する
        $current = $current[$p];
        $p = strtok('.');
}
**/
			$rules = explode('|', $rule_string);
			foreach( $rules as $rule ) {
				// rule = "key:value"
				if( strstr($rule, ':') ) {
					list($type, $pattern) = explode(':', $rule);
				} else {
					$type = $rule;
				}
				switch($type) {
					case 'required':
						if( isset($_REQUEST[$key]) ) {
							if( strlen($_REQUEST[$key]) < 1) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::MissingPropertye);
							}
						} else {
							$this->validation = false;
							return ErrorHandler::handle(ErrorHandler::MissingPropertye);
						}
					break;
					case 'format':
						if( $pattern == 'mail' ) {
							if(!filter_var($_REQUEST[$key], FILTER_VALIDATE_EMAIL)) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						} else if( $pattern == 'url' ) {
							if(!filter_var($_REQUEST[$key], FILTER_VALIDATE_URL)) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						} else if( $pattern == 'numeric' ) {
							if( !is_numeric($_REQUEST[$key]) ) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						} else if( $pattern == 'integer' ) {
							if( !is_int($_REQUEST[$key]) ) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						} else if( $pattern == 'alpha' ) {
							if( !ctype_alpha($_REQUEST[$key]) ) {
								$this->validation = false;
								return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						} else if( $pattern == 'alpha_num' ) {
							if( !ctype_digit($_REQUEST[$key]) ) {
                                                                $this->validation = false;
                                                                return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
                                                        }
						} else if( $pattern == 'alpha_dash' ) {
							if( !preg_match('/^[a-zA-Z0-9_\-]$/', $_REQUEST[$key]) ) {
                                                                $this->validation = false;
                                                                return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
                                                        }
						} else if( $patter == 'hirakanakanji' ) {
							mb_regex_encoding("UTF-8");
							if( !preg_match("/^[ぁ-んァ-ヶー一-龠]+$/u", $_REQUEST[$key])) {
								$this->validation = false;
                                                                return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
							}
						}
					break;
					case 'max':
						if( mb_strlen($_REQUEST[$key]) > $pattern ) {
							$this->validation = false;
							return ErrorHandler::handle(ErrorHandler::ParameterTooLong);
						}
					break;
					case 'min':
						if( mb_strlen($_REQUEST[$key]) < $pattern ) {
							$this->validation = false;
							return ErrorHandler::handle(ErrorHandler::ParameterTooShort);
						}
					break;
					case 'select':
						$candidates = explode(',', $pattern);
						if( !in_array($_REQUEST[$key], $candidates) ) {
							$this->validation = false;
							return ErrorHandler::handle(ErrorHandler::InvalidRequestStructure);
						}
					break;
				}
			}
		}
		return false;
	}

}
