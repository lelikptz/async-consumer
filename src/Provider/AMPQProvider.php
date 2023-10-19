<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

use lelikptz\AsyncConsumer\Promise\PromiseInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class AMPQProvider implements ProviderInterface
{
    private AMQPChannel $channel;

    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string $queue,
        private readonly TransformerInterface $transformer,
    ) {
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    public function get(): ?PromiseInterface
    {
        $message = $this->channel->basic_get($this->queue);
        if ($message !== null) {
            $message->ack();

            return $this->transformer->transform($message);
        }

        return null;
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
