<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        $this->renderable(function (HttpExceptionInterface $exception, Request $request) {

            saveErrorLog($exception->getStatusCode());

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
