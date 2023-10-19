<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Task;

enum Status
{
    case PENDING;
    case OK;
}
