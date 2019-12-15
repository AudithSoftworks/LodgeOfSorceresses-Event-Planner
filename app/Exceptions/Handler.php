<?php namespace App\Exceptions;

use App\Exceptions\Users\UserNotActivatedException;
use App\Exceptions\Users\UserNotMemberInDiscord;
use Exception;
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

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
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
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @return void
     * @throws \Exception
     */
    public function report(Exception $e): void
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        $requestExpectsJson = $request->expectsJson();
        if ($e instanceof ModelNotFoundException || $e instanceof UserNotMemberInDiscord || $e instanceof UserNotActivatedException) {
            return $requestExpectsJson
                ? response()->json(['message' => $e->getMessage() ?? 'Not found!'], $e instanceof ModelNotFoundException ? SymfonyHttpResponse::HTTP_NOT_FOUND : SymfonyHttpResponse::HTTP_FORBIDDEN)
                : redirect()->guest('/logout')->withErrors($e->getMessage());
        }

        if ($e instanceof InvalidStateException) {
            return $requestExpectsJson
                ? response()->json(['message' => 'Session Expired. Please refresh the page!'], JsonResponse::HTTP_UNAUTHORIZED)
                : redirect()->guest('/logout')->withErrors('Access Denied!');
        }

        if ($e instanceof AuthorizationException) {
            return $requestExpectsJson
                ? response()->json(['message' => $e->getMessage()], JsonResponse::HTTP_FORBIDDEN)
                : redirect()->guest('/logout')->withErrors($e->getMessage());
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
            ? response()->json(['message' => 'Session expired or No session! If you were in the middle of something, please refresh the page.'], SymfonyHttpResponse::HTTP_UNAUTHORIZED)
            : redirect()->guest('/');
    }
}
