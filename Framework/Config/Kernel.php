<?php

namespace Framework\Config;

use Framework\Config\Services\Configurator;
use Framework\Endpoint\EndpointTemplate\HttpEndpoint;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private ConfigLoader $configLoader;

    public function __construct(string $environment)
    {
        $debug = $environment === 'dev';

        if ($debug) {
            umask(0000);
            Debug::enable();
        }

        parent::__construct($environment, $debug);

        $this->configLoader = new ConfigLoader();
    }

    public function getProjectDir(): string
    {
        return realpath(__DIR__ . '/../..');
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/unused_log_dir';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache';
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $this->configLoader->importConfigs($container, $this->environment, [
            __DIR__ . '/SymfonyEnvSecret',
            __DIR__ . '/SymfonyConfig',
            __DIR__ . '/Test',
        ]);

        (new Configurator())->configureAllServices($container->services());
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $this->configLoader->importConfigs($routes, $this->environment, [
            'Routes',
        ]);

        $endpointLocations = [
            __DIR__ . '/../../Framework/Endpoint/BundleEndpoint/CheckHealthEndpoint.php',
            __DIR__ . '/../../Api/Endpoint/',
        ];
        foreach ($endpointLocations as $endpointLocation) {
            $routes->import($endpointLocation, HttpEndpoint::class);
        }
    }
}
