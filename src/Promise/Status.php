<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

enum Status
{
    case PENDING;
    case OK;
}
