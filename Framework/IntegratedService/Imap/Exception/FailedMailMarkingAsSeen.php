<?php

namespace Framework\IntegratedService\Imap\Exception;

final class FailedMailMarkingAsSeen extends \RuntimeException
{
    public function __construct(\Throwable $exception)
    {
        parent::__construct("Failed mark email as seen.", previous: $exception);
    }
}