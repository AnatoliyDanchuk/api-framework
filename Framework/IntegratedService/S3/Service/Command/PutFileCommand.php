<?php

namespace Framework\IntegratedService\S3\Service\Command;

use Framework\IntegratedService\S3\S3Client;

class PutFileCommand
{
    private S3Client $s3;

    public function __construct(S3Client $s3Client)
    {
        $this->s3 = $s3Client;
    }

    public function putFile(string $key, string $content): void
    {
        $this->s3->putObject([
            'Key' => $key,
            'Body' => $content,
        ]);
    }
}