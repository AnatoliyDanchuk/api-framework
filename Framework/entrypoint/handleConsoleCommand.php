#!/usr/bin/env php
<?php

use Framework\Config\ComposerLoader;
use Framework\Config\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

/** @noinspection SpellCheckingInspection */
if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    throw new LogicException('The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI');
}

require __DIR__ . '/../Config/ComposerLoader.php';
$composerLoader = ComposerLoader::initComposer();

$kernel = new Kernel($composerLoader, $_ENV['APP_ENV'], false);
$application = new Application($kernel);
$input = new ArgvInput();
$application->run($input);
