<?php

namespace App\Enums;

enum CardTypeEnum: int
{
    case BASE = 0;
    case USER = 1;
    case ORDER = 2;
    case TEXT = 3;
    case FINANCE = 4;
}
