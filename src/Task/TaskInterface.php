<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task;

interface TaskInterface
{
    /**
     * @return Status
     */
    public function getStatus(): Status;
}
