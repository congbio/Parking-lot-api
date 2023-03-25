<?php

namespace App\Services\Interfaces;

interface IRedisService
{
    public function setActive(int $userId): void;
    public function renewExpireTimeActive(int $userId): void;
    public function setRefreshTokenWithUserId(int $userId, string $token): void;
    public function getRefreshTokenByUserId(int $userId): ?string;
    public function addActiveMemberToConversation(int $userId, int $conId): void;
    public function getMemberOfConversation(int $conId): ?array;
    public function existsConversation(int $conId): bool;
    public function isUserInConversation(int $userId, int $conId): bool;
    public function removeMemberOfConversation(int $userId, int $conId): void;
    public function renewExpireTimeOfConversation(int $conId): void;
    public function setFullName(int $userId, string $fullName): void;
    public function getFullName(int $userId): ?string;
    public function deleteFullName(int $userId): void;
    public function setOtp($email,$otp):void;
    public function getOtp($email):int|null;
    public function setInfoRegis($dataRegis,$otp):void;
    public function getInfoRegis($otp):mixed;
    public  function deleteOtp($email): mixed;
    public function  deleteInfor($otp): mixed;
}
