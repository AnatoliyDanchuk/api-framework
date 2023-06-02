<?php

namespace Framework\IntegratedService\Imap\Exception;

use Framework\Exception\ExceptionWithContext;
use PhpImap\Exceptions\ConnectionException;

final class ImapConnectionError extends ExceptionWithContext
{
    public function __construct(string $email, ConnectionException $exception)
    {
        parent::__construct([
            'email' => $email,
        ], $exception);
    }
}