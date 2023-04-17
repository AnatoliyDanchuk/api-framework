<?php

namespace Framework\Config;

use FilesystemIterator;
use GlobIterator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class ConfigLoader
{
    private const CONFIG_FILE_EXTENSION = 'yaml';

    /**
     * @param ContainerConfigurator|RoutingConfigurator $configurator
     */
    public function importConfigs(
        $configurator,
        string $env,
        array $configDirs
    ): void
    {
        $configPathPatterns = $this->buildConfigPathPatterns($configDirs, $env);
        $actualConfigFiles = $this->getActualConfigFiles($configPathPatterns);
        foreach ($actualConfigFiles as $file) {
            $configurator->import($file);
        }
    }

    private function buildConfigPathPatterns(array $configDirs, string $env): array
    {
        $filenamePattern = '*.' . self::CONFIG_FILE_EXTENSION;
        $configPathPatterns = [];
        foreach ($configDirs as $configDir) {
            $configPathPatterns[] = $configDir . '/' . $filenamePattern;
            $configPathPatterns[] = $configDir . '/' . $env . '/' . $filenamePattern;
        }

        return $configPathPatterns;
    }

    private function getActualConfigFiles(array $configPathPatterns): array
    {
        $actualConfigFiles = [];
        foreach ($configPathPatterns as $configPathPattern) {
            $foundFiles = new GlobIterator(
                $configPathPattern,
                FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
            );
            foreach ($foundFiles as $existedFilePath) {
                $actualConfigFiles[] = $existedFilePath;
            }
        }
        return $actualConfigFiles;
    }
}