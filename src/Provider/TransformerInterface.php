<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

use lelikptz\AsyncConsumer\Promise\PromiseInterface;
use PhpAmqpLib\Message\AMQPMessage;

interface TransformerInterface
{
    public function transform(AMQPMessage $message): PromiseInterface;
}
