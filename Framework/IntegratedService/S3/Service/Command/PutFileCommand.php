<?php

namespace Framework\IntegratedService\S3\Service\Command;

use Framework\IntegratedService\S3\Service\S3Client;

class PutFileCommand
{
    public function __construct(
        private readonly S3Client $s3Client,
    )
    {
    }

    public function putFile(string $key, string $content): void
    {
        $this->s3Client->putObject([
            'Key' => $key,
            'Body' => $content,
        ]);
    }
}