<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Tests\Provider;

use lelikptz\AsyncConsumer\Provider\BatchProvider;
use lelikptz\AsyncConsumer\Provider\ProviderInterface;
use lelikptz\AsyncConsumer\Task\TaskInterface;
use PHPUnit\Framework\MockObject\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;

class BatchProviderTest extends TestCase
{
    /**
     * @throws PHPUnitException
     */
    public function testGet(): void
    {
        $provider = new BatchProvider(
            $this->createInnerProviderMock(),
            3,
            10,
            0,
        );

        $actual = $provider->get();

        $this->assertCount(3, $actual);
        foreach ($actual as $task) {
            $this->assertInstanceOf(TaskInterface::class, $task);
        }
    }

    /**
     * @throws PHPUnitException
     */
    public function testGetByTimeout(): void
    {
        $provider = new BatchProvider(
            $this->createInnerWithOneTaskOnly(),
            5,
            1,
            100000,
        );

        $actual = $provider->get();

        $this->assertCount(1, $actual);
        foreach ($actual as $task) {
            $this->assertInstanceOf(TaskInterface::class, $task);
        }
    }

    /**
     * @throws PHPUnitException
     */
    private function createInnerProviderMock(): ProviderInterface
    {
        $mock = $this->createMock(ProviderInterface::class);

        $mock->expects($this->exactly(3))
            ->method('get')
            ->willReturn([$this->createMock(TaskInterface::class)]);

        return $mock;
    }

    /**
     * @throws PHPUnitException
     */
    private function createInnerWithOneTaskOnly(): ProviderInterface
    {
        $mock = $this->createMock(ProviderInterface::class);

        $wasCall = false;
        $mock->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function () use (&$wasCall) {
                    if (!$wasCall) {
                        $wasCall = true;
                        return [$this->createMock(TaskInterface::class)];
                    }

                    return [];
                }
            );

        return $mock;
    }
}
