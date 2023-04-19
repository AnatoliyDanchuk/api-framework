<?php

namespace Framework\Test\TestTemplate;

use Composer\Autoload\ClassLoader;
use Framework\Config\ComposerLoader;
use Framework\Config\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

trait FrameworkTestKernel
{
    private static ClassLoader $composer;

    final protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        // Begin copy past from parent.
        static::$class ??= static::getKernelClass();
        // End copy past from parent.

        $composerLoader = ComposerLoader::initComposer();
        self::$composer = $composerLoader->getComposer();
        return new Kernel($composerLoader, $_ENV['APP_ENV'], true);
    }

    public static function getComposer()
    {
        return self::$composer;
    }
}