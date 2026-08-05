<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\Sweego;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class SweegoTest extends Base
{
    /**
     * @throws \Exception
     */
    public function testSendSMS(): void
    {
        $sender = new Sweego(
            apiKey: \getenv('SWEEGO_API_KEY'),
            from: \getenv('SWEEGO_FROM'),
        );

        $message = new SMS(
            to: ['0541234567'],
            content: 'Test Content',
        );

        $response = $sender->send($message);

        $this->assertResponse($response);
    }
}
