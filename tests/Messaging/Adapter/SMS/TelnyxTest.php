<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\Telnyx;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class TelnyxTest extends Base
{
    /**
     * @throws \Exception
     */
public function testSendSMS(): void
    {
        $apiKey = \getenv('TELNYX_API_KEY');
        $from = \getenv('TELNYX_FROM');

        if (empty($apiKey) || empty($from)) {
            $this->markTestSkipped('TELNYX_API_KEY and TELNYX_FROM environment variables are required');
        }

        $sender = new Telnyx($apiKey, $from);

        $message = new SMS(
            to: [\getenv('TELNYX_TO')],
            content: 'Test Content',
            from: \getenv('TELNYX_FROM')
        );

        $result = $sender->send($message);

        $this->assertResponse($result);
    }
}
