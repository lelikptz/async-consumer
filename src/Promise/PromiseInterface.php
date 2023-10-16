<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

interface PromiseInterface
{
    public function getStatus(): Status;
}
