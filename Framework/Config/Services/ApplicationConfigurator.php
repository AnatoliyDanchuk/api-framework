<?php

namespace Framework\Config\Services;

use Framework\Endpoint\EndpointServiceLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ApplicationConfigurator
{
    public function configure(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load('Domain\\', __DIR__ . '/../../../Domain/');
        $servicesConfigurator->load('DomainAdapter\\', __DIR__ . '/../../../DomainAdapter/')
            ->exclude(__DIR__ . '/../../../DomainAdapter/DataProvider');
        $servicesConfigurator->load('Api\\', __DIR__ . '/../../../Api/')
            ->public();

        $this->enableApplicationFactories($servicesConfigurator);
    }

    private function enableApplicationFactories(ServicesConfigurator $servicesConfigurator): void
    {
        $directories = [
            __DIR__ . '/../../../Api/EndpointParamSpecification',
            __DIR__ . '/../../../Api/CombinedEndpointParamSpecification',
        ];

        $endpointServiceLocator = new EndpointServiceLocator();

        foreach ($directories as $directory) {
            $paramSpecificationFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,
                \FilesystemIterator::CURRENT_AS_PATHNAME
                | \FilesystemIterator::SKIP_DOTS
            ));

            $replacement = [
                __DIR__ . '/../../../' => '',
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