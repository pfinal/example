<?php

include_once __DIR__ . '/Client.php';
include_once __DIR__ . '/Response.php';

$client = new \Rain\Http\Client();

$response = $client->get('http://www.baidu.com');

var_dump($response->getStatusCode());

var_dump($response->getHeaderRaw());

var_dump($response->getBody());

