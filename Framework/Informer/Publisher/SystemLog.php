<?php

namespace Framework\Informer\Publisher;

final class SystemLog
{
    public function publish(string $message): void
    {
        $singleLineMessage = str_replace("\n", ' ', $message);

        error_log($singleLineMessage . "\n");
    }
}