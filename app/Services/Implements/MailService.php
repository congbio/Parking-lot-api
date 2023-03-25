<?php

namespace App\Services\Implements;

use App\Mail\OTPMail;
use App\Mail\ResetPassWordMail;
use App\Mail\WelComeMail;
use App\Models\User;
use App\Services\Interfaces\MailType;
use Illuminate\Support\Facades\Mail;


class MailService implements \App\Services\Interfaces\IMailService
{
    public function sendMail(MailType $mailType, array $info = []): void
    {

        switch ($mailType) {
            default: {return;}
            case MailType::OTP_MAIL: {
                $user = $info["user"];
                $otp = $info["otp"];
                $otpMail = new OTPMail($user, $otp);
                Mail::to($user->email)->send($otpMail);
                return;
            }
            case MailType::WELCOME_MAIL:{
                $email = $info["email"];
                $welcomeMail=new WelComeMail($email);
                Mail::to($email)->send($welcomeMail);
                return;
            }
            case MailType::CHANGE_PASSWORD:{
                $email = $info["email"];
                $resetPass=new ResetPassWordMail($email);
                Mail::to($email)->send($resetPass);
            }
        }
    }
}
