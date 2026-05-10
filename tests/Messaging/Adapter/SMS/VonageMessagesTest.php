<?php

namespace Utopia\Tests\Adapter\SMS;

use PHPUnit\Framework\TestCase;
use Utopia\Messaging\Adapter\SMS\VonageMessages;
use Utopia\Messaging\Adapter\WhatsApp\Vonage as VonageWhatsApp;
use Utopia\Messaging\Adapter\Viber\Vonage as VonageViber;
use Utopia\Messaging\Adapter\MMS\Vonage as VonageMMS;
use Utopia\Messaging\Messages\SMS;

class VonageMessagesTest extends TestCase
{
    private string $applicationId = 'test-application-id';
    
    private string $privateKey = <<<'EOD'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAu1SU1LfJLPribzDKx8yKkW9Ly4a9Xj8h7c8rKmWMlG0h7QJyZ3R4
fW9p3Kz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8
zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8
zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8zKz8
-----END RSA PRIVATE KEY-----
EOD;

    public function testSendSMS(): void
    {
        $sender = new VonageMessages($this->applicationId, $this->privateKey);

        $message = new SMS(
            to: ['+1234567890'],
            content: 'Test SMS Content',
            from: 'Appwrite'
        );

        $response = $sender->send($message);

        $this->assertIsArray($response);
    }

    public function testSendWhatsApp(): void
    {
        $sender = new VonageWhatsApp($this->applicationId, $this->privateKey);

        $message = new SMS(
            to: ['+1234567890'],
            content: 'Test WhatsApp Content',
            from: 'Appwrite'
        );

        $response = $sender->send($message);

        $this->assertIsArray($response);
    }

    public function testSendViber(): void
    {
        $sender = new VonageViber($this->applicationId, $this->privateKey);

        $message = new SMS(
            to: ['+1234567890'],
            content: 'Test Viber Content',
            from: 'Appwrite'
        );

        $response = $sender->send($message);

        $this->assertIsArray($response);
    }

    public function testSendMMS(): void
    {
        $sender = new VonageMMS($this->applicationId, $this->privateKey);

        $message = new SMS(
            to: ['+1234567890'],
            content: 'Test MMS Content',
            from: 'Appwrite'
        );

        $response = $sender->send($message);

        $this->assertIsArray($response);
    }
}