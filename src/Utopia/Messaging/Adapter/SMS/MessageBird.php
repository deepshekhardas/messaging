<?php

namespace Utopia\Messaging\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS as SMSAdapter;
use Utopia\Messaging\Messages\SMS as SMSMessage;
use Utopia\Messaging\Response;

// Reference Material
// https://developers.messagebird.com/api/sms-messaging/
class MessageBird extends SMSAdapter
{
    protected const NAME = 'MessageBird';

    /**
     * @param  string  $apiKey MessageBird API key
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
            url: 'https://rest.messagebird.com/messages',
            headers: [
                'Content-Type: application/json',
                'Authorization: AccessKey '.$this->apiKey,
            ],
            body: [
                'originator' => $this->from ?? $message->getFrom(),
                'recipients' => $message->getTo(),
                'body' => $message->getContent(),
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
