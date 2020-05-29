<?php namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response as SymfonyHttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        TokenMismatchException::class,
        IlluminateValidationException::class,
        ModelNotFoundException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $e
     *
     * @return void
     * @throws \Exception
     */
    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Throwable
     */
    public function render($request, Throwable $e): SymfonyHttpResponse
    {
        $requestExpectsJson = $request->expectsJson();
        if ($e instanceof ModelNotFoundException || $e instanceof AuthorizationException) {
            return $requestExpectsJson
                ? response()->json(['message' => $e->getMessage() ?? 'Not found!'], $e instanceof ModelNotFoundException ? JsonResponse::HTTP_NOT_FOUND : JsonResponse::HTTP_FORBIDDEN)
                : redirect()->guest('/logout')->withErrors($e->getMessage());
        }

        if ($e instanceof InvalidStateException) {
            return $requestExpectsJson
                ? response()->json(['message' => 'Session Expired. Please refresh the page!'], JsonResponse::HTTP_UNAUTHORIZED)
                : redirect()->guest('/logout')->withErrors('Access Denied!');
        }

        if ($request->method() !== 'GET' && $request->header('content-type') === 'application/x-www-form-urlencoded') {
            return redirect()->back()->withInput($request->all())->withErrors($e->getMessage());
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return SymfonyHttpResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): SymfonyHttpResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'Please login.'], SymfonyHttpResponse::HTTP_UNAUTHORIZED)
            : redirect()->guest('/');
    }
}
