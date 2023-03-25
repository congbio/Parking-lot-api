<?php

namespace App\Services\Interfaces;

enum MailType
{
    case OTP_MAIL;
    case WELCOME_MAIL;
    case CHANGE_PASSWORD;
}
