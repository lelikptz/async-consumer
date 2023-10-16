<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

interface ProviderInterface
{
    public function get(): PromiseInterface;
}
