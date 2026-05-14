<?php

namespace Utopia\Messaging;

use Exception;

class Messenger
{
    /**
     * @var Adapter[]
     */
    private array $adapters;

    /**
     * @param Adapter|Adapter[] $adapters A single adapter or an array of adapters
     */
    public function __construct(Adapter|array $adapters)
    {
        $adapterArray = \is_array($adapters) ? $adapters : [$adapters];

        if (empty($adapterArray)) {
            throw new \InvalidArgumentException('At least one adapter must be provided.');
        }

        foreach ($adapterArray as $adapter) {
            if (!$adapter instanceof Adapter) {
                throw new \InvalidArgumentException('All adapters must be instances of Utopia\Messaging\Adapter.');
            }
        }

        $this->adapters = $adapterArray;
    }

    /**
     * Get the name of the messenger.
     */
    public function getName(): string
    {
        $names = \array_map(fn ($adapter) => $adapter->getName(), $this->adapters);

        return 'Messenger (' . \implode(', ', $names) . ')';
    }

    /**
     * Get the type of the adapter.
     */
    public function getType(): string
    {
        return $this->adapters[0]->getType();
    }

    /**
     * Get the type of the message the adapter can send.
     */
    public function getMessageType(): string
    {
        return $this->adapters[0]->getMessageType();
    }

    /**
     * Get the maximum number of messages that can be sent in a single request.
     * Returns the minimum across all adapters to ensure all can handle the message.
     */
    public function getMaxMessagesPerRequest(): int
    {
        $maxMessages = \array_map(
            fn ($adapter) => $adapter->getMaxMessagesPerRequest(),
            $this->adapters
        );

        return \min($maxMessages);
    }

    /**
     * Send a message using the adapters in sequence, failing over on exceptions.
     *
     * @return array{
     *     deliveredTo: int,
     *     type: string,
     *     results: array<array<string, mixed>>
     * }
     *
     * @throws Exception If all adapters fail
     */
    public function send(Message $message): array
    {
        if (!\is_a($message, $this->getMessageType())) {
            throw new \Exception('Invalid message type. Expected: ' . $this->getMessageType() . ', got: ' . \get_class($message));
        }

        if (\method_exists($message, 'getTo') && \count($message->getTo()) > $this->getMaxMessagesPerRequest()) {
            throw new \Exception('Message exceeds maximum allowed messages. Maximum: ' . $this->getMaxMessagesPerRequest());
        }

        $errors = [];
        $lastException = null;

        foreach ($this->adapters as $adapter) {
            try {
                $result = $adapter->send($message);

                if (isset($result['deliveredTo']) && $result['deliveredTo'] > 0) {
                    return $result;
                }

                $errors[] = $adapter->getName() . ' returned zero delivered messages.';
            } catch (\Exception $e) {
                $errors[] = $adapter->getName() . ': ' . $e->getMessage();
                $lastException = $e;
            }
        }

        $count = \count($this->adapters);
        if ($count === 1) {
            $errorMessage = 'Adapter failed: ' . $errors[0];
        } else {
            $errorMessage = 'All ' . $count . ' adapters failed: ' . \implode('; ', $errors);
        }

        throw new Exception($errorMessage, 0, $lastException);
    }
}