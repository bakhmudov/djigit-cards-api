<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
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
     */
    public function register(): void
    {
        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json(['error' => 'Token has expired'], 401);
        });

        $this->renderable(function (TokenInvalidException $e, $request) {
            return response()->json(['error' => 'Token is invalid'], 401);
        });

        $this->renderable(function (JWTException $e, $request) {
            return response()->json(['error' => 'Token is not provided'], 401);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
//        \Log::error('Unauthenticated exception', ['exception' => $exception]);

        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

}
