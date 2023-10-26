<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

use lelikptz\AsyncConsumer\Consumer\Exception\FailConsumerException;
use lelikptz\AsyncConsumer\Provider\ProviderInterface;
use lelikptz\AsyncConsumer\Task\Exception\FatalTaskException;
use lelikptz\AsyncConsumer\Task\Executor\ExecutorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class AsyncConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly ExecutorInterface $executor,
        private readonly int $pollTimeoutInMicroseconds,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function consume(): void
    {
        $this->logger->info('Consumer starting...');

        while (true) {
            try {
                $tasks = $this->provider->get();

                $start = time();
                if (!empty($tasks)) {
                    $this->executor->execute($tasks);
                }
                $this->logger->info(
                    sprintf(
                        'Batch with %s tasks successful completed in %s seconds',
                        count($tasks),
                        time() - $start,
                    ),
                );
            } catch (FatalTaskException $exception) {
                $this->logger->critical('Fail consumer error', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                throw new FailConsumerException($exception->getMessage(), $exception->getCode(), $exception);
            } catch (Throwable $throwable) {
                $this->logger->error('Task execute error', [
                    'message' => $throwable->getMessage(),
                    'trace' => $throwable->getTraceAsString(),
                ]);
            }
            usleep(abs($this->pollTimeoutInMicroseconds));
        }
    }
}
