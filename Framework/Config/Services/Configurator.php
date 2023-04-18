<?php

namespace Framework\Config\Services;

use Framework\Config\ComposerLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

final class Configurator
{
    public function __construct(
        private ComposerLoader $composerLoader,
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
        (new TestConfigurator())->configure($servicesConfigurator);
    }
}