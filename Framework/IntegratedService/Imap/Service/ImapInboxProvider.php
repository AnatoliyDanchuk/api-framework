<?php

namespace Framework\IntegratedService\Imap\Service;

use PhpImap\Mailbox;

final class ImapInboxProvider
{
    private Mailbox $mailbox;

    public function __construct(
        private ImapCredentialsProvider $imapCredentials
    )
    {
    }

    public function getInbox(): Mailbox
    {
        return $this->mailbox ??= $this->buildInbox();
    }

    public function buildInbox(): Mailbox
    {
        $mailbox = new Mailbox(
            '{' . $this->imapCredentials->getHost() . ':993/imap/ssl}INBOX',
            $this->imapCredentials->getLogin(),
            $this->imapCredentials->getPassword(),
        );
        $mailbox->setAttachmentsIgnore(true);
        return $mailbox;
    }
}