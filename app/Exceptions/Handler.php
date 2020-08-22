<?php namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response as SymfonyHttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    private const LOGOUT_PATH = '/logout';

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
        MaintenanceModeException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @throws \Throwable
     * @return \Symfony\Component\HttpFoundation\Response|void
     */
    public function render($request, Throwable $e)
    {
        $requestExpectsJson = $request->expectsJson();
        if ($e instanceof ModelNotFoundException || $e instanceof AuthorizationException) {
            $statusCode = $e instanceof ModelNotFoundException
                ? JsonResponse::HTTP_NOT_FOUND
                : JsonResponse::HTTP_FORBIDDEN;

            return $requestExpectsJson
                ? response()->json(['message' => $e->getMessage() ?? 'Not found!'], $statusCode)
                : redirect()->guest(self::LOGOUT_PATH)->withErrors($e->getMessage());
        }

        if ($e instanceof MaintenanceModeException) {
            $message = !empty($message = $e->getMessage())
                ? 'Service Unavailable due to Maintenance: ' . $message
                : 'Service Unavailable due to Maintenance';

            return $requestExpectsJson
                ? response()->json(['message' => 'Service Unavailable due to Maintenance.'], JsonResponse::HTTP_SERVICE_UNAVAILABLE)
                : abort(JsonResponse::HTTP_SERVICE_UNAVAILABLE, $message);
        }

        if ($e instanceof InvalidStateException) {
            return $requestExpectsJson
                ? response()->json(['message' => 'Session expired! Please re-login.'], JsonResponse::HTTP_UNAUTHORIZED)
                : redirect()->guest(self::LOGOUT_PATH)->withErrors('Session expired! Please re-login.');
        }

        if ($request->method() !== 'GET' && $request->header('content-type') === 'application/x-www-form-urlencoded') {
            return redirect()->back()->withInput($request->all())->withErrors($e->getMessage());
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return SymfonyHttpResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): SymfonyHttpResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'Please login.'], SymfonyHttpResponse::HTTP_UNAUTHORIZED)
            : redirect()->guest(self::LOGOUT_PATH)->withErrors('Session expired! Please re-login.');
    }
}
