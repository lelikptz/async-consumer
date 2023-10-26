<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Tests\Consumer;

use Exception;
use lelikptz\AsyncConsumer\Consumer\AsyncConsumer;
use lelikptz\AsyncConsumer\Consumer\Exception\FailConsumerException;
use lelikptz\AsyncConsumer\Provider\ProviderInterface;
use lelikptz\AsyncConsumer\Task\Exception\FatalTaskException;
use lelikptz\AsyncConsumer\Task\Executor\ExecutorInterface;
use lelikptz\AsyncConsumer\Task\TaskInterface;
use PHPUnit\Framework\MockObject\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AsyncConsumerTest extends TestCase
{
    /**
     * @throws PHPUnitException
     */
    public function testConsume(): void
    {
        $this->expectException(FailConsumerException::class);

        $consumer = new AsyncConsumer(
            $this->createProviderMock(),
            $this->createExecutorMock(),
            0,
            $this->createMock(LoggerInterface::class),
        );

        $consumer->consume();
    }

    /**
     * @throws PHPUnitException
     */
    private function createProviderMock(): ProviderInterface
    {
        $mock = $this->createMock(ProviderInterface::class);

        $mock->expects($this->exactly(4))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                [],
                [$this->createMock(TaskInterface::class)],
                [$this->createMock(TaskInterface::class)],
                [$this->createMock(TaskInterface::class)],
            );

        return $mock;
    }

    /**
     * @throws PHPUnitException
     */
    private function createExecutorMock(): ExecutorInterface
    {
        $mock = $this->createMock(ExecutorInterface::class);
        $counter = 0;

        $mock->expects($this->exactly(3))
            ->method('execute')
            ->willReturnCallback(
                function () use (&$counter) {
                    $counter++;
                    if ($counter === 2) {
                        throw new Exception('some exception');
                    }
                    if ($counter === 3) {
                        throw new FatalTaskException('Fatal task exception');
                    }
                }
            );

        return $mock;
    }
}
