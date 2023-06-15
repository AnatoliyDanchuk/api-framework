<?php

namespace Framework\IntegratedService\Imap\Service;

use PhpImap\Mailbox;

final class ImapInboxProvider
{
    private Mailbox $mailbox;

    public function __construct(
        private readonly ImapCredentialsProvider $imapCredentials
    )
    {
    }

    public function getMailbox(): Mailbox
    {
        return $this->mailbox ??= $this->buildInbox();
    }

    private function buildInbox(): Mailbox
    {
        $mailbox = new Mailbox(
            '{' . $this->imapCredentials->host . ':993/imap/ssl}INBOX',
            $this->imapCredentials->login,
            $this->imapCredentials->password,
        );
        $mailbox->setAttachmentsIgnore(true);
        return $mailbox;
    }
}