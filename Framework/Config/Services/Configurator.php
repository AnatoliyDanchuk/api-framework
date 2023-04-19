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
        $servicesConfigurator->defaults()
            ->autowire()
            ->autoconfigure();

        (new FrameworkConfigurator())->configure($servicesConfigurator);
        (new ApplicationConfigurator($this->composerLoader))->configure($servicesConfigurator);
        if ($this->forRunTests) {
            (new TestConfigurator())->configure($servicesConfigurator);
        }
    }
}