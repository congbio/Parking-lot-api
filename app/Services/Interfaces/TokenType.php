<?php

namespace App\Services\Interfaces;

enum TokenType
{
    case ACCESS_TOKEN;
    case REFRESH_TOKEN;
}
