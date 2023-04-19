<?php

namespace Framework\Config;

use Composer\Autoload\ClassLoader;

final class ComposerLoader
{
    private const LIBRARY_ROOT = __DIR__ . '/../..';
    private const VENDOR_ROOT = self::LIBRARY_ROOT . '/../..';
    private const VAR_ROOT = self::VENDOR_ROOT . '/..';
    private const PROJECT_ROOT = self::VAR_ROOT . '/..';

    public static function initComposer(): self
    {
        $autoloadFile = self::VENDOR_ROOT . '/autoload.php';
        $composer = require_once realpath($autoloadFile);
        return new self($composer);
    }

    private function __construct(
        private readonly ClassLoader $composer
    )
    {
    }

    public function getVarRoot(): string
    {
        return realpath(self::VAR_ROOT);
    }

    public function getProjectRoot(): string
    {
        return realpath(self::PROJECT_ROOT);
    }

    public function getComposer(): ClassLoader
    {
        return $this->composer;
    }
}