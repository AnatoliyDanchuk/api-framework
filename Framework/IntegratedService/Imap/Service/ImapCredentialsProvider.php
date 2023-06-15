<?php

namespace Framework\IntegratedService\Imap\Service;

/**
 * @final
 * This class is not final because error will be:
 * ProxyManager\Exception\InvalidProxiedClassException : Provided class
 * "Framework\IntegratedService\Imap\Service\ImapCredentialsProvider" is final and cannot be proxied
 * @readonly
 * Cannot generate lazy proxy: class "Framework\IntegratedService\Imap\Service\ImapCredentialsProvider" is readonly.
 */
class ImapCredentialsProvider
{
    public function __construct(
        public readonly string $host,
        public readonly string $login,
        public readonly string $password,
    )
    {
    }
}