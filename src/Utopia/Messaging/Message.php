<?php

namespace Utopia\Messaging;

/**
 * Marker interface for specific message types.
 */
interface Message
{
    /**
     * @return array<string>
     */
    public function getTo(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function getAttachments(): ?array;
}
