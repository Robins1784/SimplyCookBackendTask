<?php

declare(strict_types=1);

namespace Domain\Delivery;

enum Frequency: int
{
    case weekly = 7;
    case fortnightly = 14;
    case monthly = 28;
}
