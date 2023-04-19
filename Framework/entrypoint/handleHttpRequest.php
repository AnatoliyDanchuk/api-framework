<?php

use Framework\Config\ComposerLoader;
use Framework\Config\Kernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../Config/ComposerLoader.php';
$composerLoader = ComposerLoader::initComposer();

$kernel = new Kernel($composerLoader, $_ENV['APP_ENV'], false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
