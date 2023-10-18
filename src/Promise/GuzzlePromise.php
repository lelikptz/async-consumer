<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use lelikptz\AsyncConsumer\Promise\Exception\PromiseException;

final class GuzzlePromise implements PromiseInterface
{
    private CurlMultiHandler $curl;

    private Client $client;

    private ?\GuzzleHttp\Promise\PromiseInterface $promise = null;

    public function __construct(
        private readonly RequestFactoryInterface $factory,
        private readonly ResponseHandlerInterface $responseHandler,
    ) {
        $this->curl = new CurlMultiHandler();
        $this->client = new Client(['handler' => HandlerStack::create($this->curl)]);
    }

    public function start(): void
    {
        $this->promise = $this->client->sendAsync($this->factory->create());
        $this->promise->then(
            fn(Response $response) => $this->responseHandler->onSuccess($response),
            fn(RequestException $exception) => $this->responseHandler->onException(
                new PromiseException($exception->getMessage(), $exception->getCode(), $exception)
            ),
        );
    }

    public function getStatus(): Status
    {
        if ($this->promise === null) {
            throw new PromiseException('First you need to call the method start');
        }

        $this->curl->tick();
        if ($this->promise->getState() === 'pending') {
            return Status::PENDING;
        }

        return Status::OK;
    }
}
