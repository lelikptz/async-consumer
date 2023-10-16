<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use Psr\Http\Message\RequestInterface;

interface RequestFactoryInterface
{
    public function create(): RequestInterface;
}
