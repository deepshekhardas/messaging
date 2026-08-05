<?php

namespace Utopia\Messaging\Adapter\Push;

use Utopia\Messaging\Adapter\Push as PushAdapter;
use Utopia\Messaging\Messages\Push as PushMessage;
use Utopia\Messaging\Response;

// Reference Material
// https://docs.pushwoosh.com/platform-docs/pushwoosh-push-notification-basics/push-api-request
class Pushwoosh extends PushAdapter
{
    protected const NAME = 'Pushwoosh';

    public function __construct(
        private string $applicationCode,
        private string $apiToken
    ) {
        parent::__construct();
    }

    /**
     * Get adapter name.
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * Get max messages per request.
     */
    public function getMaxMessagesPerRequest(): int
    {
        return 1000;
    }

    /**
     * {@inheritdoc}
     */
    protected function process(PushMessage $message): array
    {
        $notification = [];

        if (!\is_null($message->getTitle())) {
            $notification['title'] = $message->getTitle();
        }
        if (!\is_null($message->getBody())) {
            $notification['content'] = $message->getBody();
        }
        if (!\is_null($message->getData())) {
            $notification['data'] = $message->getData();
        }
        if (!\is_null($message->getBadge())) {
            $notification['ios_badges'] = (int)$message->getBadge();
        }
        if (!\is_null($message->getSound())) {
            $notification['sound'] = $message->getSound();
        }

        $notification['send_date'] = 'now';
        $notification['devices'] = $message->getTo();

        $result = $this->request(
            method: 'POST',
            url: 'https://cp.pushwoosh.com/json/1.3/createMessage',
            headers: [
                'Content-Type: application/json',
            ],
            body: [
                'application' => $this->applicationCode,
                'auth' => $this->apiToken,
                'notifications' => [$notification],
            ],
        );

        $response = new Response($this->getType());

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
