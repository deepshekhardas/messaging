<?php

namespace Utopia\Messaging\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS as SMSAdapter;
use Utopia\Messaging\Messages\SMS as SMSMessage;
use Utopia\Messaging\Response;

class VonageMessages extends SMSAdapter
{
    protected const NAME = 'VonageMessages';

    private const API_URL = 'https://api.nexmo.com/v1/messages';

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

    protected function process(SMSMessage $message): array
    {
        $response = new Response($this->getType());

        $from = $this->from;
        if (empty($from)) {
            $from = $message->getFrom();
        }

        foreach ($message->getTo() as $to) {
            $to = \ltrim($to, '+');

            $result = $this->request(
                method: 'POST',
                url: self::API_URL,
                headers: [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . \base64_encode($this->apiKey . ':' . $this->apiSecret),
                ],
                body: [
                    'to' => $to,
                    'from' => $from,
                    'channel' => 'SMS',
                    'message_type' => 'text',
                    'text' => $message->getContent(),
                ]
            );

            if ($result['statusCode'] === 202) {
                $response->incrementDeliveredTo();
                $response->addResult($to);
            } else {
                $errorMessage = $result['response']['error'] ?? 'Unknown error';
                if (isset($result['response']['detail'])) {
                    $errorMessage = $result['response']['detail'];
                }
                $response->addResult($to, $errorMessage);
            }
        }

        return $response->toArray();
    }
}