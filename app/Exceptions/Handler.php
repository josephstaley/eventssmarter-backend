<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Sentry\Laravel\Integration;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            if (env('APP_ENV') !== 'local' && app()->bound('sentry')){
                Integration::captureUnhandledException($e);
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $ex){

        if( $request->is('api/*') ) { // for routes starting with `/api`
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 401);
        }

        return redirect('/login'); // for normal routes
    }
}
