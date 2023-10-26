<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Provider;

final class BatchProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $singleProvider,
        private readonly int $maxBatchSize,
        private readonly int $maxBatchCollectTimeInSeconds,
        private readonly int $pollTimeoutInMicroseconds,
    ) {
    }

    public function get(): array
    {
        $time = time();
        $batch = [];
        while (!$this->batchIsFilled($batch, $time)) {
            $tasks = $this->singleProvider->get();
            if (!empty($tasks)) {
                $batch[] = $tasks;
            }
            usleep(abs($this->pollTimeoutInMicroseconds));
        }

        return array_merge(...$batch);
    }

    private function batchIsFilled(array $batch, int $time): bool
    {
        return count($batch) >= $this->maxBatchSize
            || (time() - $time) > $this->maxBatchCollectTimeInSeconds;
    }
}
