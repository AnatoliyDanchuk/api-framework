<?php

namespace Framework\IntegratedService\Imap\Service;

use Framework\IntegratedService\Imap\Exception\FailedMailMarkingAsSeen;
use Framework\IntegratedService\Imap\Exception\ImapConnectionError;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use Traversable;

final class ImapInbox implements \IteratorAggregate
{
    public function __construct(
        private ImapInboxProvider $imapInboxProvider,
    )
    {
    }

    /**
     * @return Traversable|IncomingMail[]
     */
    public function getIterator(): Traversable
    {
        try {
            foreach ($this->imapInboxProvider->getInbox()->searchMailbox('UNSEEN') as $mailId) {
                yield $mailId => $this->imapInboxProvider->getInbox()->getMail($mailId, false);
            }
        } catch (ConnectionException $exception) {
            throw new ImapConnectionError($this->imapInboxProvider->getInbox()->getLogin(), $exception);
        }
    }

    public function markAsSeen(int $mailId): void
    {
        try {
            $this->imapInboxProvider->getInbox()->markMailAsRead($mailId);
        } catch(\Throwable $exception) {
            throw new FailedMailMarkingAsSeen($exception);
        }
    }
}