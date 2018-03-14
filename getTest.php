<?php 

use GuzzleHttp\Client;

require __DIR__ . '/vendor/autoload.php';

$client = new Client([
    'base_uri' => 'http://httpbin.org',
    'timeout'  => 2.0,
]);

$response = $client->request('GET', 'get');

$body = $response->getBody();
echo $body;

exit(0);
