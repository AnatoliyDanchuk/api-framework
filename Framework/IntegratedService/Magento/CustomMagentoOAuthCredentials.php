<?php

namespace Framework\IntegratedService\Magento;

use Framework\IntegratedService\Magento\Service\MagentoOAuthCredentialsInterface;

final class CustomMagentoOAuthCredentials implements MagentoOAuthCredentialsInterface
{
    private string $consumer_key;
    private string $consumer_secret;
    private string $token;
    private string $token_secret;

    public function __construct(
        string $consumer_key,
        string $consumer_secret,
        string $token,
        string $token_secret
    )
    {
        $this->token_secret = $token_secret;
        $this->token = $token;
        $this->consumer_secret = $consumer_secret;
        $this->consumer_key = $consumer_key;
    }

    public function getConsumerKey(): string
    {
        return $this->consumer_key;
    }

    public function getConsumerSecret(): string
    {
        return $this->consumer_secret;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTokenSecret(): string
    {
        return $this->token_secret;
    }
}