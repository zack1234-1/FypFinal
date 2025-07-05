<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {

        });
        if (env('APP_ENV') == 'production') {
            // Handle 403 Unauthorized exceptions
            $this->renderable(function (AuthorizationException $e, Request $request) {
                return response()->view('errors.403', [], 403);
            });

            // Handle 404 Not Found exceptions
            $this->renderable(function (NotFoundHttpException $e, Request $request) {
                return response()->view('errors.404', [], 404);
            });

            // Handle HTTP exceptions
            $this->renderable(function (HttpExceptionInterface $e, Request $request) {
                $statusCode = $e->getStatusCode();
                if (view()->exists("errors.{$statusCode}")) {
                    return response()->view("errors.{$statusCode}", [], $statusCode);
                }
                return response()->view('errors.500', [], 500);
            });

            // Handle all other exceptions
            $this->renderable(function (Throwable $e, Request $request) {
                // Log the exception for debugging purposes
                Log::error($e);

                // Render the error page
                return response()->view('errors.500', [], 500);
            });

            // Handle HTTP Response Exceptions specifically if needed
            $this->renderable(function (HttpResponseException $e, Request $request) {
                return response()->view('errors.500', [], 500);
            });
        }
    }
    // public function render($request, Throwable $exception)
    // {
    //     if ($this->isHttpException($exception)) {
    //         $status = $exception->getStatusCode();

    //         if (view()->exists("errors.{$status}")) {
    //             return response()->view("errors.{$status}", [], $status);
    //         }
    //     }

    //     return parent::render($request, $exception);
    // }
}