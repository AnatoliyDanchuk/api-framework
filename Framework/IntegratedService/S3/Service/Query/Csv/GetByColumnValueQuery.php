<?php

namespace Framework\IntegratedService\S3\Service\Query\Csv;

use Framework\JsonCoder\JsonDecoder;

final class GetByColumnValueQuery
{
    private SqlExecutor $sqlExecutor;
    private JsonDecoder $jsonDecoder;

    public function __construct(
        SqlExecutor $sqlExecutor,
        JsonDecoder $jsonDecoder
    )
    {
        $this->sqlExecutor = $sqlExecutor;
        $this->jsonDecoder = $jsonDecoder;
    }

    public function get(string $s3Key, string $columnName, string $columnValue): array
    {
        $jsonRows = $this->sqlExecutor->executeSql($s3Key,
        "SELECT * FROM S3Object WHERE $columnName = '$columnValue'",
        'JSON'
        );

        return array_map(function ($value) {
            return $this->jsonDecoder->decode($value);
        }, $jsonRows);
    }
}