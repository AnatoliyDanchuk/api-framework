<?php

namespace Framework\Test\TestTemplate;

use Framework\Config\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class KernelTestTemplate extends KernelTestCase
{
    use FrameworkTestKernel;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    final protected function getKernel(): Kernel
    {
        return self::$kernel;
    }
}