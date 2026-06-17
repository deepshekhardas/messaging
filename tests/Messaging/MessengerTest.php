<?php

namespace Utopia\Tests\Messaging;

use Utopia\Messaging\Adapter\SMS\Mock;
use Utopia\Messaging\Messenger;
use Utopia\Messaging\Messages\SMS;
use PHPUnit\Framework\TestCase;

class MessengerTest extends TestCase
{
    public function testSendWithSingleAdapter(): void
    {
        $mock = new Mock();
        $messenger = new Messenger($mock);

        $message = new SMS(to: ['+1234567890'], content: 'Test');

        $result = $messenger->send($message);

        $this->assertEquals(1, $result['deliveredTo']);
        $this->assertEquals('sms', $result['type']);
    }

    public function testSendWithMultipleAdaptersSuccessfulFirst(): void
    {
        $mock1 = new Mock();
        $mock2 = new Mock();
        $messenger = new Messenger([$mock1, $mock2]);

        $message = new SMS(to: ['+1234567890'], content: 'Test');

        $result = $messenger->send($message);

        $this->assertEquals(1, $result['deliveredTo']);
    }

    public function testSendWithMultipleAdaptersFailover(): void
    {
        $mock1 = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock1->method('getName')->willReturn('Mock1');
        $mock1->method('getType')->willReturn('sms');
        $mock1->method('getMessageType')->willReturn(SMS::class);
        $mock1->method('getMaxMessagesPerRequest')->willReturn(100);
        $mock1->method('send')->willThrowException(new \Exception('Failed'));

        $mock2 = new Mock();
        $messenger = new Messenger([$mock1, $mock2]);

        $message = new SMS(to: ['+1234567890'], content: 'Test');

        $result = $messenger->send($message);

        $this->assertEquals(1, $result['deliveredTo']);
    }

    public function testSendWithAllAdaptersFail(): void
    {
        $mock1 = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock1->method('getName')->willReturn('Mock1');
        $mock1->method('getType')->willReturn('sms');
        $mock1->method('getMessageType')->willReturn(SMS::class);
        $mock1->method('getMaxMessagesPerRequest')->willReturn(100);
        $mock1->method('send')->willThrowException(new \Exception('Failed 1'));

        $mock2 = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock2->method('getName')->willReturn('Mock2');
        $mock2->method('getType')->willReturn('sms');
        $mock2->method('getMessageType')->willReturn(SMS::class);
        $mock2->method('getMaxMessagesPerRequest')->willReturn(100);
        $mock2->method('send')->willThrowException(new \Exception('Failed 2'));

        $messenger = new Messenger([$mock1, $mock2]);

        $message = new SMS(to: ['+1234567890'], content: 'Test');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('All 2 adapters failed');

        $messenger->send($message);
    }

    public function testInvalidAdapterType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All adapters must be instances of Utopia\Messaging\Adapter');

        new Messenger(['not an adapter']);
    }

    public function testEmptyAdapters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one adapter must be provided');

        new Messenger([]);
    }

    public function testGetName(): void
    {
        $mock1 = new Mock();
        $mock2 = new Mock();
        $messenger = new Messenger([$mock1, $mock2]);

        $name = $messenger->getName();

        $this->assertStringContainsString('Messenger', $name);
        $this->assertStringContainsString('Mock', $name);
    }

    public function testGetMaxMessagesPerRequest(): void
    {
        $mock1 = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock1->method('getMaxMessagesPerRequest')->willReturn(10);

        $mock2 = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock2->method('getMaxMessagesPerRequest')->willReturn(5);

        $messenger = new Messenger([$mock1, $mock2]);

        $this->assertEquals(5, $messenger->getMaxMessagesPerRequest());
    }

    public function testGetType(): void
    {
        $mock = new Mock();
        $messenger = new Messenger($mock);

        $this->assertEquals('sms', $messenger->getType());
    }

    public function testGetMessageType(): void
    {
        $mock = new Mock();
        $messenger = new Messenger($mock);

        $this->assertEquals(SMS::class, $messenger->getMessageType());
    }

    public function testMessageExceedsMaxMessages(): void
    {
        $mock = $this->createMock(\Utopia\Messaging\Adapter\SMS::class);
        $mock->method('getName')->willReturn('Mock');
        $mock->method('getType')->willReturn('sms');
        $mock->method('getMessageType')->willReturn(SMS::class);
        $mock->method('getMaxMessagesPerRequest')->willReturn(1);

        $messenger = new Messenger($mock);

        $message = new SMS(to: ['+1234567890', '+0987654321'], content: 'Test');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Message exceeds maximum allowed messages');

        $messenger->send($message);
    }

    public function testInvalidMessageType(): void
    {
        $mock = new Mock();
        $messenger = new Messenger($mock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type');

        $messenger->send(new \Utopia\Messaging\Messages\Email(
            to: ['test@example.com'],
            subject: 'Test',
            html: '<p>Test</p>'
        ));
    }

    public function testSingleAdapterConstruction(): void
    {
        $mock = new Mock();
        $messenger = new Messenger($mock);

        $this->assertInstanceOf(Messenger::class, $messenger);
    }

    public function testArrayAdapterConstruction(): void
    {
        $mock1 = new Mock();
        $mock2 = new Mock();
        $messenger = new Messenger([$mock1, $mock2]);

        $this->assertInstanceOf(Messenger::class, $messenger);
    }
}