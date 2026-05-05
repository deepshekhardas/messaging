<?php

namespace Utopia\Messaging\Adapter\SMS;

// Reference Material
// https://www.textmagic.com/docs/api/send-sms/#How-to-send-bulk-text-messages

use Utopia\Messaging\Adapter\SMS as SMSAdapter;
use Utopia\Messaging\Messages\SMS;
use Utopia\Messaging\Response;

class Vonage extends SMSAdapter
{
    protected const NAME = 'Vonage';

    /**
     * @param  string  $apiKey Vonage API Key
     * @param  string  $apiSecret Vonage API Secret
     */
    public function __construct(
        private string $apiKey,
        private string $apiSecret,
        private ?string $from = null
    ) {
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getMaxMessagesPerRequest(): int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    protected function process(SMS $message): array
    {
        $recipients = $message->getTo();
        if (empty($recipients)) {
            throw new \Exception('No recipients provided');
        }

        $to = \array_map(
            fn ($to) => \ltrim($to, '+'),
            $recipients
        );

        $response = new Response($this->getType());
        $result = $this->request(
            method: 'POST',
            url: 'https://rest.nexmo.com/sms/json',
            headers: [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            body: [
                'text' => $message->getContent(),
                'from' => $this->from ?? $message->getFrom(),
                'to' => $to[0], //\implode(',', $to),
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ],
        );

        $messages = $result['response']['messages'] ?? [];
        if (!empty($messages) && ($messages[0]['status'] ?? null) === 0) {
            $response->setDeliveredTo(1);
            $response->addResult($messages[0]['to'] ?? $recipients[0]);
        } else {
            $error = $messages[0]['error-text'] ?? 'Unknown error';
            $response->addResult($recipients[0], $error);
        }

        return $response->toArray();
    }
}
