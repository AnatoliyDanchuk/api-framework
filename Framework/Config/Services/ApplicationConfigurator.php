<?php

namespace Framework\Config\Services;

use Framework\Config\ComposerLoader;
use Framework\Endpoint\EndpointServiceLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ApplicationConfigurator
{
    public function __construct(
        private ComposerLoader $composerLoader,
    )
    {
    }

    public function configure(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load('Domain\\', $this->composerLoader->getProjectRoot() . '/Domain/');
        $servicesConfigurator->load('DomainAdapter\\', $this->composerLoader->getProjectRoot() . '/DomainAdapter/')
            ->exclude($this->composerLoader->getProjectRoot() . '/DomainAdapter/DataProvider');
        $servicesConfigurator->load('Api\\', $this->composerLoader->getProjectRoot() . '/Api/')
            ->public();

        $this->enableApplicationFactories($servicesConfigurator);
    }

    private function enableApplicationFactories(ServicesConfigurator $servicesConfigurator): void
    {
        $directories = [
            $this->composerLoader->getProjectRoot() . '/Api/EndpointParamSpecification',
            $this->composerLoader->getProjectRoot() . '/Api/CombinedEndpointParamSpecification',
        ];

        $endpointServiceLocator = new EndpointServiceLocator();

        foreach ($directories as $directory) {
            $paramSpecificationFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,
                \FilesystemIterator::CURRENT_AS_PATHNAME
                | \FilesystemIterator::SKIP_DOTS
            ));

            $replacement = [
                $this->composerLoader->getProjectRoot() => '',
                '.php' => '',
                '/' => '\\',
            ];
            foreach ($paramSpecificationFiles as $paramSpecificationFile) {
                $paramSpecificationClass = str_replace(array_keys($replacement), array_values($replacement), $paramSpecificationFile);
                if (!$endpointServiceLocator->isSupport($paramSpecificationClass)) {
                    continue;
                }

                $interface = $endpointServiceLocator->getInterface($paramSpecificationClass);
                [$factoryClass, $factoryMethod] = [EndpointServiceLocator::class, 'getService'];
                $servicesConfigurator->set($interface)
                    ->factory([service($factoryClass), $factoryMethod])
                    ->args([$interface])
                    ->lazy();
            }
        }
    }
}