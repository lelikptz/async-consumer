<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task\Http;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerInterface
{
    public function onSuccess(ResponseInterface $response): void;

    public function onException(RequestException $exception): void;
}
