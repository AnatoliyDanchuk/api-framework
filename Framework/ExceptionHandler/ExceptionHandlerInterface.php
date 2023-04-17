<?php

namespace Framework\ExceptionHandler;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

interface ExceptionHandlerInterface
{
    public function onKernelException(ExceptionEvent $event): void;
}
