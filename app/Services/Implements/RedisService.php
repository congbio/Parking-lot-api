<?php

namespace App\Services\Implements;

use App\Services\Interfaces\IRedisService;
use Illuminate\Support\Facades\Redis;

class RedisService implements IRedisService
{
    public function setActive(int $userId): void
    {
       Redis::set("Active:$userId", true);
        Redis::expire("Active:$userId", 3*60);
    }

    public function renewExpireTimeActive(int $userId): void
    {
        Redis::expire("Active:$userId", 3*60);
    }
        public function setRefreshTokenWithUserId(int $userId, string $token): void
    {
        Redis::set("User:Refresh:$userId", $token);
        Redis::expire("User:Refresh:$userId", env("REFRESH_TOKEN_EXPIRED_TIME")/1000);
    }

    public function getRefreshTokenByUserId(int $userId): ?string
    {
        return Redis::get("Refresh:$userId");
    }

    public function addActiveMemberToConversation(int $userId, int $conId): void
    {
        Redis::sadd("ConversationMembers:$conId", $userId);
        Redis::expire("ConversationMembers:$conId", 10*60);
    }

    public function getMemberOfConversation(int $conId): ?array
    {
        return Redis::smembers("ConversationMembers:$conId");
    }
    public function existsConversation(int $conId): bool {
        return Redis::exists("ConversationMembers:$conId");
    }
    public function isUserInConversation(int $userId, int $conId): bool {
        return Redis::sismember("ConversationMembers:$conId", $userId);
    }
    public function removeMemberOfConversation(int $userId, int $conId): void
    {
        Redis::srem("ConversationMembers:$conId", $userId);
    }
    public function renewExpireTimeOfConversation(int $conId): void
    {
       Redis::expire("ConversationMembers:$conId", 10*60);
    }
    public function setFullName(int $userId, string $fullName): void
    {
        Redis::set("User:FullName:$userId", $fullName);
    }

    public function getFullName(int $userId): ?string
    {
        return Redis::get("User:FullName:$userId");
    }

    public function deleteFullName(int $userId): void
    {
        Redis::del("User:FullName:$userId");
    }
    public function setOtp($email,$otp):  void
    {
        Redis::set("Otp:$email",$otp);
        Redis::expire("Otp:$email", 5*60);
    }
    public function getOtp($email): int
    {
        return Redis::get("Otp:$email");
    }

    public  function  setInfoRegis($dataRegis,$otp): void
    {
        $user = json_encode($dataRegis);
       Redis::set("InforRegis:$otp",$user);

    }
    public function  getInfoRegis($otp): mixed
    {
       return json_decode(Redis::get("InforRegis:$otp"));
    }
    public  function deleteOtp($email): mixed
    {
        Redis::del("Otp:$email");
        return "ok";
    }
    public function  deleteInfor($otp): mixed
    {
        Redis::del("InforRegis:$otp");
        return "ok";
    }
}
