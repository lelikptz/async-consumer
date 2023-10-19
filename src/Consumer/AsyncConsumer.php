<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

use Fiber;
use lelikptz\AsyncConsumer\Promise\PromiseInterface;
use lelikptz\AsyncConsumer\Promise\Status;
use lelikptz\AsyncConsumer\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class AsyncConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly int $concurrency,
        private readonly int $maxBatchCollectTimeInSeconds,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function consume(): void
    {
        $this->logger->info('Consumer starting...');

        while (true) {
            $time = time();
            $batch = [];
            while (!$this->batchIsFilled($batch, $time)) {
                $promise = $this->provider->get();
                if ($promise !== null) {
                    $promise->start();
                    $batch[] = $promise;
                }
                usleep(100000);
            }

            try {
                $start = time();
                $this->execute($batch);
                $this->logger->info(
                    sprintf(
                        'Batch with %s tasks successful completed in %s seconds',
                        count($batch),
                        time() - $start,
                    ),
                );
            } catch (Throwable $throwable) {
                $this->logger->error('Batch execute error', [
                    'message' => $throwable->getMessage(),
                    'trace' => $throwable->getTraceAsString(),
                ]);
            }
            usleep(300000);
        }
    }

    private function batchIsFilled(array $batch, int $time): bool
    {
        return count($batch) === $this->concurrency
            || count($batch) > 0 && (time() - $time) > $this->maxBatchCollectTimeInSeconds;
    }

    /**
     * @param PromiseInterface[] $batch
     * @throws Throwable
     */
    private function execute(array $batch): void
    {
        $fibers = [];
        foreach ($batch as $promise) {
            $fiber = new Fiber(function (PromiseInterface $promise) {
                do {
                    Fiber::suspend();
                    usleep(1000);
                } while ($promise->getStatus() === Status::PENDING);
            });
            $fiber->start($promise);

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
