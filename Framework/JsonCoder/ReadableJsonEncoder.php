<?php

namespace Framework\JsonCoder;

final class ReadableJsonEncoder
{
    private const ENCODE_FLAGS = \JSON_PRETTY_PRINT
    | \JSON_NUMERIC_CHECK
    | \JSON_UNESCAPED_SLASHES
    | \JSON_UNESCAPED_UNICODE
    | \JSON_UNESCAPED_LINE_TERMINATORS
    | \JSON_THROW_ON_ERROR;

    public function encode($input): string
    {
        return json_encode($input, self::ENCODE_FLAGS);
    }
}