#!/usr/bin/env php
<?php

use Framework\Config\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

/** @noinspection SpellCheckingInspection */
if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    throw new LogicException('The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI');
}

require dirname(__DIR__).'/../var/vendor/autoload.php';

$kernel = new Kernel($_ENV['APP_ENV']);
$application = new Application($kernel);
$input = new ArgvInput();
$application->run($input);
