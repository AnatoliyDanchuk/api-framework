<?php

namespace Framework\IntegratedService\S3\Service\Query\Csv;

use Framework\IntegratedService\S3\Service\S3Client;
use GuzzleHttp\Psr7\Stream;

final class SqlExecutor
{
    public function __construct(
        private readonly S3Client $s3Client,
    )
    {
    }

    public function executeSql(string $key, string $sql, string $outputSerializationFormat): array
    {
        $result = $this->s3Client->selectObjectContent([
            'Key' => $key,
            'ExpressionType' => 'SQL',
            'Expression' => $sql,
            'InputSerialization' => [
                'CSV' => [
                    'FileHeaderInfo' => 'USE',
                    'RecordDelimiter' => PHP_EOL,
                    'FieldDelimiter' => ',',
                ],
            ],
            'OutputSerialization' => [
                $outputSerializationFormat => [],
            ],
        ]);

        foreach ($result['Payload'] as $event) {
            if (isset($event['Records'])) {
                /** @var Stream $stream */
                $foundStreamWithData = $event['Records']['Payload'];
                break;
            }
        }

        return isset($foundStreamWithData)
            ? explode(PHP_EOL, rtrim($foundStreamWithData, PHP_EOL))
            : [];
    }
}