<?php

namespace App\Utils;

use Illuminate\Http\Request;

class RequestUtils
{
    public static function getAccessTokenFromRequest(Request $request): string|null {
        $authorization = $request->header("Authorization");
        if ($authorization) {
            $accessToken = explode(" ", $authorization)[1];
            if ($accessToken) {
                return $accessToken;
            }
        }
        return null;
    }
}
