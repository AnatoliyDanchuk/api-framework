<?php

namespace Framework\Test\TestTemplate;

use Framework\Config\Kernel;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use function uopz_set_hook;
use function uopz_unset_hook;

abstract class MockEndpointTestTemplate extends KernelTestTemplate
{
    public function setUp(): void
    {
        $mockEndpointDirPath = $this->getMockEndpointDirPath();

        mkdir($mockEndpointDirPath, 0777, true);

        parent::getComposer()->setPsr4("TestEndpoint\\", $mockEndpointDirPath .'/');

        uopz_set_hook(Kernel::class, 'configureRoutes', function (RoutingConfigurator $routes)
        use ($mockEndpointDirPath): void {
            $routes->import($mockEndpointDirPath, 'HttpEndpoint');
        });

        $testServiceYamlFile = $this->getTempTestDir() . '/test.yaml';
        uopz_set_hook(Kernel::class, 'configureContainer', function(ContainerConfigurator $container)
        use ($testServiceYamlFile): void {
            $container->import($testServiceYamlFile);
        });
        /** @noinspection SpellCheckingInspection */
        file_put_contents($testServiceYamlFile, <<<YAML
        services:
            _defaults:
                autowire: true      # Automatically injects dependencies in your services.
                autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.
            
            TestEndpoint\:
                resource: '$mockEndpointDirPath/'
                # It is public because HttpEndpointLoader load it by container.
                public: true
        YAML);

        parent::setUp();
    }

    public function tearDown(): void
    {
        system('rm -rf -- ' . $this->getTempTestDir());

        uopz_unset_hook(Kernel::class, 'configureRoutes');
        uopz_unset_hook(Kernel::class, 'configureContainer');

        parent::tearDown();
    }

    private function getMockEndpointDirPath(): string
    {
        return $this->getTempTestDir() . '/TestEndpoint';
    }

    private function getMockWarmupDir(): string
    {
        return $this->getTempTestDir() . '/var';
    }

    private function getTempTestDir(): string
    {
        static $dir = [];
        $shortName = (new ReflectionClass($this))->getShortName();
        $dir[$shortName] ??= sys_get_temp_dir() . '/' . uniqid($shortName);
        return $dir[$shortName];
    }

    final protected function getTestShortName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    final protected function createEndpoint(string $endpointSpecification, array $paramClasses): string
    {
        $endpointClassName = uniqid('TestEndpoint');

        $constructorParams = [];
        $expectedParams = [];
        foreach ($paramClasses as $index => $paramClass) {
            $constructorParams[] = "private \\$paramClass \$uniqueParam$index,";
            $expectedParams[] = "\$this->uniqueParam$index,";
        }
        $constructorParamsPhp = implode('', $constructorParams);
        $expectedParamsPhp = implode('', $expectedParams);

        $vendorSpecificationClass = $this->createVendorSpecification();

        file_put_contents($this->getMockEndpointDirPath() . "/$endpointClassName.php", <<<PHP
            <?php
            namespace TestEndpoint;
            
            use Framework\Endpoint\EndpointInput\ExpectedInput;
            use Framework\Endpoint\EndpointInput\FilledExpectedInput;

            final class $endpointClassName extends \\$endpointSpecification
            {
                public function __construct(
                    $constructorParamsPhp
                    private \\$vendorSpecificationClass \$vendorSpecification,
                ){}
                
                protected function buildExpectedInput(): ExpectedInput
                {
                    return new ExpectedInput($expectedParamsPhp);
                }
            
                protected function getVendorSpecification(): \\$vendorSpecificationClass
                {
                    return \$this->vendorSpecification;
                }
            
                protected function executeEndpoint(FilledExpectedInput \$input): array
                {
                    return [];
                }
            }
            PHP
        );

        return "TestEndpoint\\$endpointClassName";
    }

    final protected function createEndpointSpecification(?string $path=null): string
    {
        $path ??= $this->generateUniquePath();
        $endpointSpecificationClassName = uniqid('TestEndpointSpecification');

        file_put_contents($this->getMockEndpointDirPath() . "/$endpointSpecificationClassName.php", <<<PHP
            <?php
            namespace TestEndpoint;
            
            use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpointTemplate;
            
            abstract class $endpointSpecificationClassName extends ApplicationHttpEndpointTemplate
            {
                final protected function getHttpPath(): string
                {
                    return '$path';
                }
            }
            PHP
        );

        return "TestEndpoint\\$endpointSpecificationClassName";
    }

    final protected function generateUniquePath(): string
    {
        return uniqid('/');
    }

    final protected function createVendorSpecification(): string
    {
        $vendorSpecificationClassName = uniqid('TestVendorSpecification');

        file_put_contents($this->getMockEndpointDirPath() . "/$vendorSpecificationClassName.php", <<<PHP
            <?php
            namespace TestEndpoint;
            
            use Framework\Endpoint\EndpointInput\ExpectedInput;
            use Framework\Endpoint\EndpointInput\FilledExpectedInput;
            use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

            final class $vendorSpecificationClassName
                implements VendorSpecification
            {
                public function buildExpectedInput(): ExpectedInput
                {
                    return new ExpectedInput();
                }
            
                public function applyVendorSpecification(FilledExpectedInput \$appliedInput): void
                {
                }
            }
            PHP
        );

        return "TestEndpoint\\$vendorSpecificationClassName";
    }

    final protected function createParamClass(): array
    {
        $paramClass = uniqid('TestParam');
        file_put_contents($this->getMockEndpointDirPath() . "/$paramClass.php", <<<PHP
            <?php
            namespace TestEndpoint;
            
            use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
            use Symfony\Component\Validator\Constraints;
            
            final class $paramClass extends EndpointParamSpecificationTemplate
            {
                public function getParamName(): string
                {
                    return "$paramClass";
                }
            
                protected function getParamConstraints(): array
                {
                    return [];
                }
            
                public function parseValue(string \$value): ?string
                {
                    return null;
                }
            }
            PHP
        );

        return [
            "TestEndpoint\\$paramClass",
            $paramClass
        ];
    }

    final protected function applyCreatedEndpoints(): void
    {
        $this->getKernel()->reboot($this->getMockWarmupDir());
        $application = new Application($this->getKernel());
        $command = $application->find('cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([], [
            'capture_stderr_separately' => true,
        ]);
    }
}