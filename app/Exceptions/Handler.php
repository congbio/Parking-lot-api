<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(function (QueryException $e, $req) {
            return $this->responseErrorWithDetails(
                "exception.common.query",
                [$e]
            );
        });
//        $this->renderable(function (ExpiredException $e, $req) {
//            return $this->responseError(
//                "exception.common.token.expired",
//                Response::HTTP_UNAUTHORIZED
//            );
//        });
//        $this->renderable(function (SignatureInvalidException $e, $req) {
//            return $this->responseError(
//                "exception.common.token.invalid-signature",
//                Response::HTTP_UNAUTHORIZED
//            );
//        });
//        $this->renderable(function (\DomainException $e, $req) {
//            return $this->responseError(
//                "exception.common.token.validate-wrong",
//                Response::HTTP_UNAUTHORIZED
//            );
//        });
//        $this->renderable(function (\InvalidArgumentException $e, $req) {
//            return $this->responseError(
//                "exception.common.token.wrong-key",
//                Response::HTTP_UNAUTHORIZED
//            );
//        });
    }
}
