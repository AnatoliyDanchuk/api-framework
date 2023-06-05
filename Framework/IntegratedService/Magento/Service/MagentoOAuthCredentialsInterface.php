<?php

namespace Framework\IntegratedService\Magento\Service;

interface MagentoOAuthCredentialsInterface
{
    public function getConsumerKey(): string;
    public function getConsumerSecret(): string;
    public function getToken(): string;
    public function getTokenSecret(): string;
}