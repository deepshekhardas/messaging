<?php

namespace Utopia\Messaging\Adapter\MMS;

use Utopia\Messaging\Adapter;
use Utopia\Messaging\Message;
use Utopia\Messaging\Messages\SMS as SMSMessage;

abstract class MMS extends Adapter
{
    protected const TYPE = 'mms';
    protected const MESSAGE_TYPE = SMSMessage::class;

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getMessageType(): string
    {
        return static::MESSAGE_TYPE;
    }

    abstract protected function process(Message $message): array;
}