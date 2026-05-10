<?php

namespace Utopia\Messaging\Adapter\Viber;

use Utopia\Messaging\Adapter;
use Utopia\Messaging\Message;
use Utopia\Messaging\Messages\SMS as SMSMessage;

abstract class Viber extends Adapter
{
    protected const TYPE = 'viber';
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