<?php

namespace Utopia\Messaging\Adapter;

use Utopia\Messaging\Adapter;
use Utopia\Messaging\Message;
use Utopia\Messaging\Messages\Email as EmailMessage;

abstract class Email extends Adapter
{
    protected const TYPE = 'email';
    protected const MESSAGE_TYPE = EmailMessage::class;

    protected const MAX_ATTACHMENT_BYTES = 25 * 1024 * 1024; // 25MB

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getMessageType(): string
    {
        return static::MESSAGE_TYPE;
    }

    /**
     * Validate email attachments.
     *
     * @param EmailMessage $message
     * @throws \Exception
     */
    protected function validateAttachments(EmailMessage $message): void
    {
        if (\is_null($message->getAttachments())) {
            return;
        }

        $size = 0;

        foreach ($message->getAttachments() as $attachment) {
            if (!\file_exists($attachment->getPath())) {
                throw new \Exception("Attachment file not found: {$attachment->getPath()}");
            }

            $size += \filesize($attachment->getPath());
        }

        if ($size > static::MAX_ATTACHMENT_BYTES) {
            $maxSize = static::MAX_ATTACHMENT_BYTES / 1024 / 1024;
            throw new \Exception("Attachments size exceeds the maximum allowed size of {$maxSize}MB");
        }
    }

    /**
     * Process an email message.
     *
     * @return array{deliveredTo: int, type: string, results: array<array<string, mixed>>}
     *
     * @throws \Exception
     */
    abstract protected function process(Message $message): array;
}
