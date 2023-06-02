<?php

namespace Framework\IntegratedService\Imap\Service;

/**
 * @final
 * This class is not final because error will be:
 * ProxyManager\Exception\InvalidProxiedClassException : Provided class
 * "Framework\IntegratedService\Imap\Service\ImapCredentialsProvider" is final and cannot be proxied
 */
class ImapCredentialsProvider
{
    public function __construct(
        private string $host,
        private string $login,
        private string $password,
    )
    {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}