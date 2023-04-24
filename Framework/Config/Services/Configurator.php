<?php

namespace Framework\Config\Services;

use Framework\Config\ComposerLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

final class Configurator
{
    public function __construct(
        private readonly ComposerLoader $composerLoader,
        private readonly bool $forRunTests,
    )
    {
    }

    public function configureAllServices(ServicesConfigurator $servicesConfigurator): void
    {
        $defaultsConfigurator = $servicesConfigurator->defaults()
            ->autowire()
            ->autoconfigure();

        if ($this->forRunTests) {
            $defaultsConfigurator->public();
        }

        (new FrameworkConfigurator())->configure($servicesConfigurator);
        (new ApplicationConfigurator($this->composerLoader))->configure($servicesConfigurator);
    }
}