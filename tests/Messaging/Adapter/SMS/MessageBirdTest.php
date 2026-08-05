<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\MessageBird;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class MessageBirdTest extends Base
{
    /**
     * @throws \Exception
     */
    public function testSendSMS(): void
    {
        $sender = new MessageBird(
            apiKey: \getenv('MESSAGEBIRD_API_KEY'),
            from: \getenv('MESSAGEBIRD_FROM'),
        );

        $message = new SMS(
            to: ['0541234567'],
            content: 'Test Content',
        );

        $response = $sender->send($message);

        $this->assertResponse($response);
    }
}
