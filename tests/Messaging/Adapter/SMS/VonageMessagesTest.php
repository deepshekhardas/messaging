<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\VonageMessages;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class VonageMessagesTest extends Base
{
    public function testSendSMS(): void
    {
        $apiKey = \getenv('VONAGE_MESSAGES_API_KEY');
        $apiSecret = \getenv('VONAGE_MESSAGES_API_SECRET');
        $to = \getenv('VONAGE_MESSAGES_TO');

        if (empty($apiKey) || empty($apiSecret) || empty($to)) {
            $this->markTestSkipped('Vonage Messages API credentials are not available.');
        }

        $sender = new VonageMessages($apiKey, $apiSecret);

        $message = new SMS(
            to: [$to],
            content: 'Test Content',
            from: \getenv('VONAGE_MESSAGES_FROM') ?: null
        );

        $response = $sender->send($message);

        $result = \json_decode($response, true);

        $this->assertResponse($result);
    }

    public function testSendSMSWithFrom(): void
    {
        $apiKey = \getenv('VONAGE_MESSAGES_API_KEY');
        $apiSecret = \getenv('VONAGE_MESSAGES_API_SECRET');
        $to = \getenv('VONAGE_MESSAGES_TO');
        $from = \getenv('VONAGE_MESSAGES_FROM');

        if (empty($apiKey) || empty($apiSecret) || empty($to)) {
            $this->markTestSkipped('Vonage Messages API credentials are not available.');
        }

        $sender = new VonageMessages($apiKey, $apiSecret, $from ?: 'Vonage');

        $message = new SMS(
            to: [$to],
            content: 'Test Content with custom from'
        );

        $response = $sender->send($message);

        $result = \json_decode($response, true);

        $this->assertResponse($result);
    }
}