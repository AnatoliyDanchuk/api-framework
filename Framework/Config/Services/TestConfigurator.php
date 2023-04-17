<?php

namespace Framework\Config\Services;

use Framework\IntegratedService\S3\Command\PutFileCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

class TestConfigurator
{
    public function configure(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->set(PutFileCommand::class)->public();
    }
}