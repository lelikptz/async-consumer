<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task\Executor;

use lelikptz\AsyncConsumer\Task\Exception\FatalTaskException;
use lelikptz\AsyncConsumer\Task\TaskInterface;
use Throwable;

interface ExecutorInterface
{
    /**
     * @param TaskInterface[] $tasks
     * @throws FatalTaskException|Throwable
     */
    public function execute(array $tasks): void;
}
