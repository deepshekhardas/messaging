<?php

namespace Utopia\Messaging\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS as SMSAdapter;
use Utopia\Messaging\Messages\SMS as SMSMessage;
use Utopia\Messaging\Response;

// Reference Material
// https://docs.sweego.io/sms
class Sweego extends SMSAdapter
{
    protected const NAME = 'Sweego';

    /**
     * @param  string  $apiKey Sweego API key
     */
    public function __construct(
        private string $apiKey,
        private ?string $from = null
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getMaxMessagesPerRequest(): int
    {
        return 100;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function process(SMSMessage $message): array
    {
        $response = new Response($this->getType());

        $result = $this->request(
            method: 'POST',
            url: 'https://api.sweego.io/v1/sms',
            headers: [
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->apiKey,
            ],
            body: [
                'to' => $message->getTo(),
                'text' => $message->getContent(),
                'sender' => $this->from ?? $message->getFrom(),
            ],
        );

        if ($result['statusCode'] >= 200 && $result['statusCode'] < 300) {
            $response->setDeliveredTo(\count($message->getTo()));
            foreach ($message->getTo() as $to) {
                $response->addResult($to);
            }
        } else {
            foreach ($message->getTo() as $to) {
                $response->addResult($to, 'Unknown error.');
            }
        }

        return $response->toArray();
    }
}
