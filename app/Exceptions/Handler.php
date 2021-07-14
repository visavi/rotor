<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
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
        $this->reportable(function (Throwable $exception) {
            $statusCode = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

            saveErrorLog($statusCode, $exception->getMessage());
        });

        $this->renderable(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->isJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => $exception->getMessage(),
                ], $exception->getStatusCode());
            }

            if (! view()->exists('errors.' . $exception->getStatusCode())) {
                return response()->view('errors.default', compact('exception'));
            }

            return parent::renderHttpException($exception);
        });
    }
}
