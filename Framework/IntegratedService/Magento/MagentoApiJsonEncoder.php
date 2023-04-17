<?php

namespace Framework\IntegratedService\Magento;

final class MagentoApiJsonEncoder
{
    private const ENCODE_FLAGS =
        \JSON_THROW_ON_ERROR
        | \JSON_UNESCAPED_SLASHES
        | \JSON_UNESCAPED_UNICODE;

    public function encode($input): string
    {
        return json_encode($input, self::ENCODE_FLAGS);
    }
}