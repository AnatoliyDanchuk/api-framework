<?php

namespace Framework\IntegratedService\S3\Service\Query;

use Aws\S3\Exception\S3Exception;
use Framework\IntegratedService\S3\Exception\S3FileNotFound;
use Framework\IntegratedService\S3\Service\S3Client;

final class GetFileContentQuery
{
    private S3Client $s3;

    public function __construct(S3Client $s3Client)
    {
        $this->s3 = $s3Client;
    }

    public function getFileContent(string $key): string
    {
        try {
            return $this->s3->getObject([
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