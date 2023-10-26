<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

use lelikptz\AsyncConsumer\Consumer\Exception\FailConsumerException;

interface ConsumerInterface
{
    /**
     * @throws FailConsumerException
     */
    public function consume(): void;
}
