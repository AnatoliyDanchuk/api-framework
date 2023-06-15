<?php

namespace Framework\IntegratedService\Imap\Service;

/**
 * @final
 * This class is not final because error will be:
 * ProxyManager\Exception\InvalidProxiedClassException : Provided class
 * "Framework\IntegratedService\Imap\Service\ImapCredentialsProvider" is final and cannot be proxied
 */
readonly class ImapCredentialsProvider
{
    public function __construct(
        public string $host,
        public string $login,
        public string $password,
    )
    {
    }
}