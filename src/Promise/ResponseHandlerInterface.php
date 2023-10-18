<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use lelikptz\AsyncConsumer\Promise\Exception\PromiseException;
use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerInterface
{
    public function onSuccess(ResponseInterface $response): void;

    public function onException(PromiseException $exception): void;
}
