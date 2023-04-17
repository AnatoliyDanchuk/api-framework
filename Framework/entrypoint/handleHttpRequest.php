<?php

use Framework\Config\Kernel;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/../var/vendor/autoload.php';

$kernel = new Kernel($_ENV['APP_ENV']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
