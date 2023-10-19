<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

use lelikptz\AsyncConsumer\Promise\PromiseInterface;

interface ProviderInterface
{
    public function get(): ?PromiseInterface;
}
