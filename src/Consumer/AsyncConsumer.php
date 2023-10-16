<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

use Fiber;
use lelikptz\AsyncConsumer\Promise\PromiseInterface;
use lelikptz\AsyncConsumer\Promise\ProviderInterface;
use lelikptz\AsyncConsumer\Promise\Status;
use Psr\Log\LoggerInterface;
use Throwable;

final class AsyncConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly int $concurrency,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function consume(): void
    {
        while (true) {
            $time = time();
            $batch = [];
            while (count($batch) < $this->concurrency) {
                $batch[] = $this->provider->get();
            }

            $fibers = [];
            foreach ($batch as $promise) {
                $fiber = new Fiber(function (PromiseInterface $promise) {
                    $this->execute($promise);
                });
                $fiber->start($promise);

                $fibers[] = $fiber;
            }
            $this->wait($fibers);
            $this->logger->info(sprintf('Batch completed in %s seconds', time() - $time));
        }
    }

    /**
     * @throws Throwable
     */
    private function execute(PromiseInterface $promise): void
    {
        do {
            Fiber::suspend();
            usleep(1000);
        } while ($promise->getStatus() === Status::PENDING);
    }

    /**
     * @param Fiber[] $fibers
     * @throws Throwable
     */
    public function wait(array $fibers): void
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
