<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\VonageLegacy;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class VonageLegacyTest extends Base
{
    public function testSendSMS(): void
    {
        $apiKey = \getenv('VONAGE_API_KEY');
        $apiSecret = \getenv('VONAGE_API_SECRET');
        $to = \getenv('VONAGE_TO');

        if (empty($apiKey) || empty($apiSecret) || empty($to)) {
            $this->markTestSkipped('Vonage credentials are not available.');
        }

        $sender = new VonageLegacy($apiKey, $apiSecret);

        $message = new SMS(
            to: [$to],
            content: 'Test Content',
            from: \getenv('VONAGE_FROM') ?: null
        );

        $response = $sender->send($message);

        $result = \json_decode($response, true);

        $this->assertResponse($result);
    }
}