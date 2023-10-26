<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

use lelikptz\AsyncConsumer\Task\TaskInterface;

interface ProviderInterface
{
    /**
     * @return TaskInterface[]
     */
    public function get(): array;
}
