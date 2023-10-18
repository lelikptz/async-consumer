<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use lelikptz\AsyncConsumer\Promise\Exception\PromiseException;

interface PromiseInterface
{
    public function start(): void;

    /**
     * @return Status
     * @throws PromiseException
     */
    public function getStatus(): Status;
}
