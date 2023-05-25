<?php

namespace Framework\IntegratedService\S3\Service;

use Aws\Result;

final readonly class S3Client
{
    public function __construct(
        private AwsS3ClientProvider $awsS3ClientProvider,
        private S3ConfigProvider $s3ConfigProvider,
    )
    {
    }

    public function getObject(array $args = []): Result
    {
        return $this->awsS3ClientProvider->getAwsS3Client()->getObject($args + [
            'Bucket' => $this->s3ConfigProvider->getBucket(),
        ]);
    }

    public function selectObjectContent(array $args = []): Result
    {
        return $this->awsS3ClientProvider->getAwsS3Client()->selectObjectContent($args + [
            'Bucket' => $this->s3ConfigProvider->getBucket(),
        ]);
    }

    public function putObject(array $args = []): void
    {
        $this->awsS3ClientProvider->getAwsS3Client()->putObject($args + [
            'Bucket' => $this->s3ConfigProvider->getBucket(),
        ]);
    }
}