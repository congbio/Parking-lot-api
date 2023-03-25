<?php
namespace App\Services\Interfaces;
use App\Models\User;

interface IMailService {
    public function sendMail(MailType $mailType, array $info = []);
}
