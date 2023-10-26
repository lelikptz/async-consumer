<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task\Executor;

use Fiber;
use lelikptz\AsyncConsumer\Task\Status;
use lelikptz\AsyncConsumer\Task\TaskInterface;
use Throwable;

final class FiberExecutor implements ExecutorInterface
{
    public function execute(array $tasks): void
    {
        $fibers = [];
        foreach ($tasks as $task) {
            $fiber = new Fiber(function (TaskInterface $task) {
                do {
                    Fiber::suspend();
                    usleep(1000);
                } while ($task->getStatus() === Status::PENDING);
            });
            $fiber->start($task);

            $fibers[] = $fiber;
        }
        $this->wait($fibers);
    }

    /**
     * @param Fiber[] $fibers
     * @throws Throwable
     */
    private function wait(array $fibers): void
    {
        while (count($fibers)) {
            usleep(1000);
            foreach ($fibers as $key => $fiber) {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                } else {
                    if ($fiber->isTerminated()) {
                        unset($fibers[$key]);
                    }
                }
            }
        }
    }
}
