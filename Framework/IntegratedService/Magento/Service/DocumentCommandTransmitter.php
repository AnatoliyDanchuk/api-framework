<?php

namespace Framework\IntegratedService\Magento\Service;

use Framework\JsonCoder\JsonDecoder;
use OAuth;
use OAuthException;
use Throwable;

final class DocumentCommandTransmitter
{
    private MagentoApiJsonEncoder $jsonEncoder;
    private JsonDecoder $jsonDecoder;
    private MagentoOAuthCredentialsInterface $credentials;
    private OAuth $commandClient;

    public function __construct(
        MagentoOAuthCredentialsInterface $credentials
    )
    {
        $this->jsonEncoder = new MagentoApiJsonEncoder();
        $this->jsonDecoder = new JsonDecoder();
        $this->credentials = $credentials;
    }

    private function createOAuth(): OAuth
    {
        $oAuth = new OAuth($this->credentials->getConsumerKey(), $this->credentials->getConsumerSecret());
        $oAuth->setToken($this->credentials->getToken(), $this->credentials->getTokenSecret());
        // For local debug enable next:
//         $oAuth->disableSSLChecks();
//         $oAuth->enableDebug();

        return $oAuth;
    }

    public function transmit(string $httpMethod, $url, array $data): string
    {
        $this->commandClient ??= $this->createOAuth();

        try {
            $this->commandClient->fetch($url, $this->jsonEncoder->encode($data), $httpMethod, [
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ]);
            $answer = $this->commandClient->getLastResponse();
            try {
                $jsonDecoded = $this->jsonDecoder->decode($answer);
                $commandHasSuccess = property_exists($jsonDecoded, 'status') && $jsonDecoded->status === "Ok";
                if (!$commandHasSuccess) {
                    $errorSection = $jsonDecoded->errors;
                    $commandError = $this->formatErrors($errorSection, $answer);
                }
            } catch(\Throwable $exception) {
                $commandError = $answer;
            }
        } catch(OAuthException $exception) {
            $transmissionError = $exception->lastResponse ?? $exception->getMessage();
        } catch (Throwable $exception) {
            $transmissionError = $exception->getMessage();
        }

        return $transmissionError ?? $commandError ?? '';
    }

    public function backup(): array
    {
        return [
            $this->credentials->getConsumerKey(),
            $this->credentials->getConsumerSecret(),
            $this->credentials->getToken(),
            $this->credentials->getTokenSecret(),
        ];
    }

    public static function restore(array $backup): self
    {
        return new self(new CustomMagentoOAuthCredentials(...$backup));
    }

    private function formatErrors($errorSection, string $answer): string
    {
        if (is_scalar($errorSection)) {
            return (string)$errorSection;
        }

        if (is_object($errorSection)) {
            $errorsAsArray = array_values(get_object_vars($errorSection));
        }
        $errorsAsArray ??= $errorSection;
        foreach ($errorsAsArray as $error) {
            if (!is_scalar($error)) {
                return $answer;
            }
        }
        return \implode("\n", $errorsAsArray);
    }
}