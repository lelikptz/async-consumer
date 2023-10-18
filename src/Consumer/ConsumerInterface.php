<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

interface ConsumerInterface
{
    public function consume(): void;
}
