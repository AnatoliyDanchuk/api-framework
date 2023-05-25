<?php

namespace Framework\IntegratedService\S3\Service\Query;

use Aws\S3\Exception\S3Exception;
use Framework\IntegratedService\S3\Exception\S3FileNotFound;
use Framework\IntegratedService\S3\Service\S3Client;

final class GetFileContentQuery
{
    public function __construct(
        private readonly S3Client $s3Client,
    )
    {
    }

    public function getFileContent(string $key): string
    {
        try {
            return $this->s3Client->getObject([
                'Key' => $key,
            ])->get('Body');
        } catch (S3Exception $exception) {
            if ($exception->getAwsErrorCode() === 'NoSuchKey') {
                throw new S3FileNotFound($exception);
            }

            throw $exception;
        }
    }
}