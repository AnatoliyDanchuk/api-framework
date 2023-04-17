<?php

namespace Framework\IntegratedService\S3;

use Aws\Credentials\Credentials;

final class S3Config
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