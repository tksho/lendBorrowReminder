<?php

require __DIR__ . '/../common.php';

$url = parse_url($_SERVER['REQUEST_URI']);

/**
$test = new stdClass();
$test->id =0;
print json_encode($test);
exit;
**/
// If request has _method then check

if( isset( $_REQUEST['_method'] ) ) {
	if( preg_match('/get|post|patch|delete/', strtolower($_REQUEST['_method']) ) ) {
		$_SERVER['REQUEST_METHOD'] = $_REQUEST['_method'];
	}
}
if( !Route::match( strtolower($_SERVER['REQUEST_METHOD']), $url['path'])  ) {
        header("HTTP/1.0 404 Not Found");
	print "ERRO";
}
