<?php

namespace Framework\JsonCoder;

final class JsonDecoder
{
    private const DECODE_FLAGS = \JSON_THROW_ON_ERROR;

    /**
     * @return mixed
     */
    public function decode(string $jsonString)
    {
        return json_decode($jsonString, null, 512, self::DECODE_FLAGS);
    }
}