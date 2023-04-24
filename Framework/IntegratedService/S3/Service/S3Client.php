<?php

namespace Framework\IntegratedService\S3\Service;

use Aws\Result;
use Aws\S3\S3Client as AwsS3;
use Aws\S3\S3MultiRegionClient;
use Framework\IntegratedService\S3\S3Config;

/**
 * @final
 * This class is not final because error will be:
 * ProxyManager\Exception\InvalidProxiedClassException : Provided class
 * "Framework\IntegratedService\S3\S3Client" is final and cannot be proxied
 */
class S3Client
{
    private AwsS3 $awsS3;
    private string $bucket;

    public function __construct(S3Config $s3Config)
    {
        $this->awsS3 = new AwsS3([
            'version' => 'latest',
            'region' => $this->getRegion($s3Config),
            'credentials' => $s3Config->getCredentials(),
        ]);

        $this->bucket = $s3Config->getBucket();
    }

    private function getRegion(S3Config $s3Config): string
    {
        $region = $s3Config->getRegion();
        if (empty($region)) {
            $awsMultiRegionS3 = new S3MultiRegionClient([
                'version' => 'latest',
                'credentials' => $s3Config->getCredentials(),
            ]);

            $region = $awsMultiRegionS3->getBucketLocation([
                'Bucket' => $s3Config->getBucket()
            ])->get('LocationConstraint');
        }

        return $region;
    }

    public function getObject(array $args = []): Result
    {
        return $this->awsS3->getObject($args + [
                'Bucket' => $this->bucket,
            ]);
    }

    public function selectObjectContent(array $args = []): Result
    {
        return $this->awsS3->selectObjectContent($args + [
            'Bucket' => $this->bucket,
        ]);
    }

    public function putObject(array $args = []): void
    {
        $this->awsS3->putObject($args + [
            'Bucket' => $this->bucket,
        ]);
    }
}