<?php

namespace Framework\Config\Services;

use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

final class Configurator
{
    public function configureAllServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->defaults()
            ->autowire()
            ->autoconfigure();

        (new FrameworkConfigurator())->configure($servicesConfigurator);
        (new ApplicationConfigurator())->configure($servicesConfigurator);
        (new TestConfigurator())->configure($servicesConfigurator);
    }
}