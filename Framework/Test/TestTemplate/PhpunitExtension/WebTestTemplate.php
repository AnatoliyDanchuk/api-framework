<?php

namespace Framework\Test\TestTemplate\PhpunitExtension;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class WebTestTemplate extends WebTestCase
{
    use ArrayMatching;

    private KernelBrowser $browser;

    final public function setUp(): void
    {
        $this->browser = static::createClient();
    }

    final protected function getBrowser(): KernelBrowser
    {
        return $this->browser;
    }
}