<?php

namespace Framework\IntegratedService\Magento;

use parallel\Future;
use parallel\Runtime;

final class ParallelCommandTransmitter
{
    private DocumentCommandTransmitter $commandTransmitter;
    private array $commandTransmitterBackup;

    public function __construct(
        DocumentCommandTransmitter $commandTransmitter
    )
    {
        $this->commandTransmitter = $commandTransmitter;
    }

    public function handle(Runtime $runtime, string $httpMethod, string $url, $commandParam): Future
    {
        $this->commandTransmitterBackup ??= $this->commandTransmitter->backup();
        $commandTransmitterBackup = $this->commandTransmitterBackup;
        return $runtime->run(function() use ($httpMethod, $url, $commandTransmitterBackup, $commandParam) : ?string {
            $commandTransmitter = DocumentCommandTransmitter::restore($commandTransmitterBackup);
            return $commandTransmitter->transmit($httpMethod, $url, $commandParam);
        });
    }
}