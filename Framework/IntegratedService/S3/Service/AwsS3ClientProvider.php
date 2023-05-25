<?php

namespace Framework\IntegratedService\S3\Service;

use Aws\S3\S3Client as AwsS3;
use Aws\S3\S3MultiRegionClient;

final class AwsS3ClientProvider
{
    private AwsS3 $awsS3;

    public function __construct(
        private readonly S3ConfigProvider $s3ConfigProvider
    )
    {
    }

    public function getAwsS3Client(): AwsS3
    {
        return $this->awsS3 ??= new AwsS3([
            'version' => 'latest',
            'region' => $this->getRegion(),
            'credentials' => $this->s3ConfigProvider->getCredentials(),
        ]);
    }

    private function getRegion(): string
    {
        $region = $this->s3ConfigProvider->getRegion();
        if (empty($region)) {
            $awsMultiRegionS3 = new S3MultiRegionClient([
                'version' => 'latest',
                'credentials' => $this->s3ConfigProvider->getCredentials(),
            ]);

            $region = $awsMultiRegionS3->getBucketLocation([
                'Bucket' => $this->s3ConfigProvider->getBucket()
            ])->get('LocationConstraint');
        }

        return $region;
    }
}