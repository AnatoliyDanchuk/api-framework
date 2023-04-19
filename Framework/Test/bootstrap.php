<?php

use Framework\Config\ComposerLoader;
use Framework\Config\Kernel;

require_once __DIR__ . '/../Config/ComposerLoader.php';

(function() {
    $composerLoader = ComposerLoader::initComposer();
    $cacheDir = (new Kernel($composerLoader, $_ENV['APP_ENV'], true))->getCacheDir();
    exec("rm -f -r $cacheDir/*");
})();
