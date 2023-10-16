<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Consumer;

use Exception;

interface ConsumerInterface
{
    /**
     * @throws Exception
     */
    public function consume(): void;
}
