<?php

namespace Utopia\Messaging\Adapter\Email;

use Utopia\Messaging\Adapter\Email as EmailAdapter;
use Utopia\Messaging\Messages\Email as EmailMessage;
use Utopia\Messaging\Response;

class Mandrill extends EmailAdapter
{
    protected const NAME = 'Mandrill';

    public function __construct(
        private readonly string $apiKey,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getMaxMessagesPerRequest(): int
    {
        return 1;
    }

    protected function process(EmailMessage $message): array
    {
        $response = new Response($this->getType());

        $result = $this->request(
            method: 'POST',
            url: 'https://mandrillapp.com/api/1.0/messages/send',
            headers: [
                'Content-Type: application/json',
            ],
            body: [
                'key' => $this->apiKey,
                'message' => [
                    'subject' => $message->getSubject(),
                    'html' => $message->isHtml() ? $message->getContent() : null,
                    'text' => $message->isHtml() ? null : $message->getContent(),
                    'from_email' => $message->getFromEmail(),
                    'from_name' => $message->getFromName(),
                    'to' => array_map(
                        fn(array $to) => ['email' => $to['email'], 'name' => $to['name'] ?? ''],
                        $message->getTo()
                    ),
                ],
            ],
        );

        $statusCode = $result['statusCode'];
        $responseBody = $result['response'];

        if ($statusCode >= 200 && $statusCode < 300) {
            $response->setDeliveredTo(\count($message->getTo()));
            foreach ($message->getTo() as $to) {
                $response->addResult($to['email']);
            }
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            $error = '';
            if (\is_array($responseBody) && isset($responseBody['message'])) {
                $error = $responseBody['message'];
            } elseif (\is_string($responseBody)) {
                $error = $responseBody;
            }

            foreach ($message->getTo() as $to) {
                $response->addResult($to['email'], $error ?: 'Unknown error');
            }
        }

        return $response->toArray();
    }
}
