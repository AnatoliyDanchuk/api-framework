<?php

namespace Framework\Exception;

final class EndpointException extends FrameworkException
{
    private array $appliedInput;
    private DomainException $caughtException;

    public function __construct(
        array $appliedInput,
        DomainException $caughtException
    ) {
        $this->caughtException = $caughtException;
        $this->appliedInput = $appliedInput;
        parent::__construct($caughtException);
    }

    public function getEndpointAppliedInput(): array
    {
        return $this->appliedInput;
    }

    public function getDomainException(): DomainException
    {
        return $this->caughtException;
    }
}
