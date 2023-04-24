<?php

namespace Framework\IntegratedService\S3\Service\Query\Csv;

final class GetColumnValuesQuery
{
    private SqlExecutor $sqlExecutor;

    public function __construct(SqlExecutor $sqlExecutor)
    {
        $this->sqlExecutor = $sqlExecutor;
    }

    public function getValues(string $s3Key, string $columnName): array
    {
        return $this->sqlExecutor->executeSql($s3Key,
            "SELECT $columnName FROM S3Object",
            'CSV'
        );
    }
}