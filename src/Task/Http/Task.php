<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use lelikptz\AsyncConsumer\Task\Status;
use lelikptz\AsyncConsumer\Task\TaskInterface;

final class Task implements TaskInterface
{
    private CurlMultiHandler $curl;

    private Client $client;

    private ?PromiseInterface $promise = null;

    public function __construct(
        private readonly RequestFactoryInterface $factory,
        private readonly ResponseHandlerInterface $responseHandler,
    ) {
        $this->curl = new CurlMultiHandler();
        $this->client = new Client(['handler' => HandlerStack::create($this->curl)]);
    }

    public function getStatus(): Status
    {
        if ($this->promise === null) {
            $this->promise = $this->client->sendAsync($this->factory->create());
            $this->promise->then(
                fn (Response $response) => $this->responseHandler->onSuccess($response),
                fn (RequestException $exception) => $this->responseHandler->onException($exception),
            );
        }

        $this->curl->tick();
        if ($this->promise->getState() === 'pending') {
            return Status::PENDING;
        }

        return Status::OK;
    }
}
