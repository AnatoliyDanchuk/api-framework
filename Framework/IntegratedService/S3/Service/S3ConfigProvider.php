<?php

namespace Framework\IntegratedService\S3\Service;

use Aws\Credentials\Credentials;

/**
 * @final
 * This class is not final because error will be:
 * ProxyManager\Exception\InvalidProxiedClassException : Provided class
 * "Framework\IntegratedService\S3\Service\S3ConfigProvider" is final and cannot be proxied
 */
class S3ConfigProvider
{
    private Credentials $credentials;
    private string $region;
    private string $bucket;

    public function __construct(
        Credentials $credentials,
        string $region,
        string $bucket
    )
    {
        $this->credentials = $credentials;
        $this->region = $region;
        $this->bucket = $bucket;
    }

    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }
}