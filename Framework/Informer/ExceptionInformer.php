<?php

namespace Framework\Informer;

use Framework\Exception\ExceptionWithContext;
use Framework\Informer\Publisher\SystemLog;
use Framework\JsonCoder\JsonEncoder;
use Throwable;

final class ExceptionInformer
{
    private SystemLog $publisher;
    private JsonEncoder $jsonSerializer;

    public function __construct(
        SystemLog $publisher,
        JsonEncoder $jsonSerializer
    ) {
        $this->publisher = $publisher;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function inform(Throwable $exception): void
    {
        $info = $this->getInfo($exception);
        $json = $this->jsonSerializer->encode($info);
        $this->publisher->publish($json);
    }

    private function getInfo(Throwable $exception): array
    {
        $totalInfo = [];

        $currentException = $exception;
        do {
            $currentInfo = [
                'exceptionClass' => get_class($currentException),
                'message' => $currentException->getMessage(),
                'context' => $currentException instanceof ExceptionWithContext
                    ? $currentException->getContext()
                    : [],
                'file' => $currentException->getFile(),
                'line' => $currentException->getLine(),
            ];
            $totalInfo[] = array_filter($currentInfo);
        } while ($currentException = $currentException->getPrevious());

        return array_filter($totalInfo);
    }
}