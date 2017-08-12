<?php

require_once __DIR__ . '/../common.php';
/////////////////////////////////////////////////////////
// How to write route file
//
// Route::get('/route/{slug}', callback[ text or function ])->where(array( slug => pattern, ... ))

Route::get('/', 'TestController@index');
Route::post('/webhook', 'WebhookController@index');
Route::get('/webhook_push', 'PushController@index');

