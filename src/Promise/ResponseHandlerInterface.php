<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerInterface
{
    public function handle(ResponseInterface $response): void;
}
