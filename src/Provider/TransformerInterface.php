<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

use lelikptz\AsyncConsumer\Task\TaskInterface;
use PhpAmqpLib\Message\AMQPMessage;

interface TransformerInterface
{
    public function transform(AMQPMessage $message): TaskInterface;
}
