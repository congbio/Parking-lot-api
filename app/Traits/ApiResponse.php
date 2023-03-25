<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    public function responseSuccess(string $message = "Successful!", int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(["message" => $message], $status);
    }

    public function responseSuccessWithData(string $message = "Successful!", array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "data" => $data
        ], $status);
    }

    public function responseError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(compact("message"), $status);
    }
    public function responseErrorWithDetails(string $message, array $errors = [], int $status = Response::HTTP_BAD_REQUEST): JsonResponse {
        return response()->json(
            [
                "message" => $message,
                "errors" => $errors
            ],
            $status
        );
    }
}
