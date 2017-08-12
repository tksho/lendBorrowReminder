<?php

class ErrorHandler {

	const InvalidRequestStructure = array( 'id'=> 'invalid_request', 'code' => 1, 'status' => 400, 'message' => 'The client provided an invalid format of request');
	const InvalidRequest = array( 'id'=> 'invalid_request_id', 'code' => 2, 'status' => 400, 'message' => 'The client provided an invalid request');
	const AuthenticationRequired = array( 'id'=> 'authentication_required', 'code' => 3, 'status' => 401, 'message' => 'The client requested an authorization required');
	const InvalidOperation = array( 'id'=> 'invalid_operation', 'code' => 4, 'status' => 422, 'message' => 'The client declined to perform invalid operation');
	const MissingPropertye = array( 'id'=> 'missing_property', 'code' => 5, 'status' => 422, 'message' => 'The client has not supplied required property');
	const ParameterTooShort = array( 'id'=> 'parameter_short', 'code' => 7, 'status' => 422, 'message' => 'The client provided value is too short');
	const ParameterTooLong = array( 'id'=> 'parameter_long', 'code' => , 'status' => 422, 'message' => 'The client provided value is too long');

	function __construct($error) {
	}

	function __destruct() {

	}

	public static function handle($error, $option = array()) {

		// 2つの連想配列を結合
		$error = array_merge($error, $option);

		// statusに合わせて処理を分岐
		switch($error["status"]) {
			case 400 :
				header("HTTP/1.0 400 Bad Request");
				break;
			case 401 :
				header("HTTP/1.0 401 Unauthorized");
				break;
			case 403 :
                                header("HTTP/1.0 403 Forbidden");
                                break;
			case 404 :
				header("HTTP/1.0 404 Not Found");
				break;
                        case 405 :
                                header("HTTP/1.0 405 Method Not Allowed");
                                break;
			case 406 :
				header("HTTP/1.0 406 Not Acceptable");
				break;
			case 408 :
				header("HTTP/1.0 408 Request Timeout");
				break;
			case 409 :
				header("HTTP/1.0 409 Conflict");
				break;
                        case 422 :
                                header("HTTP/1.0 422 Unprocessable Entity");
                                break;
                        case 429 :
                                header("HTTP/1.0 429 Too Many Requests");
                                break;
			case 404 :
				header("HTTP/1.0 404 Not Found");
				break;
			case 503 :
				header("HTTP/1.0 503 Service Unavailable");
				break;
		}

		header('Content-type:application/json; charset=utf-8');
		print json_encode($error);
		return;
	}
}
