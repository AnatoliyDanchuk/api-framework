<?php

namespace Framework\Test\TestTemplate;

abstract class EndpointTestTemplate extends FrameworkWebTestTemplate
{
    final public function testSuccessRequest(): void
    {
        $bodyParams = $this->getValidBodyArray();

        foreach ($this->getMockServices() as $serviceClass => $mockService) {
            parent::getContainer()->set($serviceClass, $mockService);
        }

        $this->prepare();
        $this->getBrowser()->request($this->getHttpMethod(), $this->getHttpPath(), content: json_encode($bodyParams));
        $this->assertFrameworkOutput($bodyParams, $this->getEndpointOutputExpectation());

        $this->prepare();
        $this->getBrowser()->request($this->getHttpMethod(), '/defer' . $this->getHttpPath(), content: json_encode($bodyParams));
        $this->assertFrameworkOutput($bodyParams, $this->getDeferEndpointOutputExpectation());
    }

    abstract protected function getValidBodyArray(): array;

    abstract protected function getMockServices(): array;

    abstract protected function prepare(): void;

    abstract protected function getHttpMethod(): string;

    abstract protected function getHttpPath(): string;

    private function assertFrameworkOutput(array $params, array $outputExpectation): void
    {
        $this->assertResponseStatusCodeSame(200);
        $answer = $this->getAnswer();

        $this->assertArray($outputExpectation, $answer['output']);
    }

    private function getAnswer(): array
    {
        $json = $this->getBrowser()->getResponse()->getContent();
        $this->assertJson($json);
        return json_decode($json, true);
    }

    abstract protected function getEndpointOutputExpectation(): array;
    abstract protected function getDeferEndpointOutputExpectation(): array;

    /**
     * @dataProvider exceptionSimulationProvider
     */
    final public function testExceptionRequest(
        callable $exceptionSimulationEnabler,
        string $exceptionMessage,
        callable $exceptionSimulationDisabler,
    ): void
    {
        if (empty($this->getExceptionSimulations())) {
            $this->assertTrue(true);
            return;
        }

        $exceptionSimulationEnabler();

        $bodyParams = $this->getValidBodyArray();
        $this->getBrowser()->request($this->getHttpMethod(), $this->getHttpPath(), content: json_encode($bodyParams));

        $exceptionSimulationDisabler();

        $this->assertFrameworkException($bodyParams, $exceptionMessage);
    }

    final public function exceptionSimulationProvider(): array
    {
        return $this->getExceptionSimulations() ?: $this->getProviderToNotMarkTestWithSkipStatus();
    }

    abstract protected function getExceptionSimulations(): array;

    private function getProviderToNotMarkTestWithSkipStatus(): array
    {
        return [
            'not have any public exceptions' => [function() {}, '', function() {}]
        ];
    }

    private function assertFrameworkException(array $params, string $exceptionMessage): void
    {
        $this->assertResponseStatusCodeSame(404);
        $answer = $this->getAnswer();
        $this->assertArray([
            'input' => [
                'appliedExpectedParams' => $params,
                'unusedPossibleParams' => [
                ],
                'ignoredUnexpectedParams' => []],
            'exception' => $exceptionMessage
        ], $answer);
    }
}