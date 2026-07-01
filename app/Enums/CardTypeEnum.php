<?php

namespace App\Enums;

enum CardTypeEnum: int
{
    case BASE = 0;
    case TASK = 1;
    case CLIENT = 2;
    case TEXT = 3;
    case FINANCE = 4;
    case DEVELOPMENT = 5;
    case ORDER = 6;
}
