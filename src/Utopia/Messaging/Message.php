<?php

namespace Utopia\Messaging;

/**
 * Marker interface for specific message types.
 */
interface Message
{
    public function getTo(): array;

    public function getAttachments(): ?array;
    public function getTo(): array;

    public function getAttachments(): ?array;
}
