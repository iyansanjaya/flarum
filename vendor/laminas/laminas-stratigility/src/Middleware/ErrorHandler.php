<?php

declare(strict_types=1);

namespace Laminas\Stratigility\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function error_reporting;
use function in_array;
use function restore_error_handler;
use function set_error_handler;

/**
 * Error handler middleware.
 *
 * Use this middleware as the outermost (or close to outermost) middleware
 * layer, and use it to intercept PHP errors and exceptions.
 *
 * The class offers two extension points:
 *
 * - Error response generators.
 * - Listeners.
 *
 * Error response generators are callables with the following signature:
 *
 * <code>
 * function (
 *     Throwable $e,
 *     ServerRequestInterface $request,
 *     ResponseInterface $response
 * ) : ResponseInterface
 * </code>
 *
 * These are provided the error, and the request responsible; the response
 * provided is the response prototype provided to the ErrorHandler instance
 * itself, and can be used as the basis for returning an error response.
 *
 * An error response generator must be provided as a constructor argument;
 * if not provided, an instance of Laminas\Stratigility\Middleware\ErrorResponseGenerator
 * will be used.
 *
 * Listeners use the following signature:
 *
 * <code>
 * function (
 *     Throwable $e,
 *     ServerRequestInterface $request,
 *     ResponseInterface $response
 * ) : void
 * </code>
 *
 * Listeners are given the error, the request responsible, and the generated
 * error response, and can then react to them. They are best suited for
 * logging and monitoring purposes.
 *
 * Listeners are attached using the attachListener() method, and triggered
 * in the order attached.
 *
 * @final
 */
class ErrorHandler implements MiddlewareInterface
{
    /** @var callable[] */
    private array $listeners = [];

    /** @var callable Routine that will generate the error response. */
    private $responseGenerator;

    /**
     * @param null|callable $responseGenerator Callback that will generate the final
     *     error response; if none is provided, ErrorResponseGenerator is used.
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        ?callable $responseGenerator = null
    ) {
        $this->responseGenerator = $responseGenerator ?? new ErrorResponseGenerator();
    }

    /**
     * Attach an error listener.
     *
     * Each listener receives the following three arguments:
     *
     * - Throwable $error
     * - ServerRequestInterface $request
     * - ResponseInterface $response
     *
     * These instances are all immutable, and the return values of
     * listeners are ignored; use listeners for reporting purposes
     * only.
     */
    public function attachListener(callable $listener): void
    {
        if (in_array($listener, $this->listeners, true)) {
            return;
        }

        $this->listeners[] = $listener;
    }

    /**
     * Middleware to handle errors and exceptions in layers it wraps.
     *
     * Adds an error handler that will convert PHP errors to ErrorException
     * instances.
     *
     * Internally, wraps the call to $next() in a try/catch block, catching
     * all PHP Throwables.
     *
     * When an exception is caught, an appropriate error response is created
     * and returned instead; otherwise, the response returned by $next is
     * used.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        set_error_handler($this->createErrorHandler());

        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $response = $this->handleThrowable($e, $request);
        }

        restore_error_handler();

        return $response;
    }

    /**
     * Handles all throwables, generating and returning a response.
     *
     * Passes the error, request, and response prototype to createErrorResponse(),
     * triggers all listeners with the same arguments (but using the response
     * returned from createErrorResponse()), and then returns the response.
     */
    private function handleThrowable(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $generator = $this->responseGenerator;
        /** @var ResponseInterface $response */
        $response = $generator($e, $request, $this->responseFactory->createResponse());
        $this->triggerListeners($e, $request, $response);
        return $response;
    }

    /**
     * Creates and returns a callable error handler that raises exceptions.
     *
     * Only raises exceptions for errors that are within the error_reporting mask.
     *
     * @return callable(int, string, string=, int=, array<array-key, mixed>=): bool
     */
    private function createErrorHandler(): callable
    {
        /**
         * @throws ErrorException if error is not within the error_reporting mask.
         * @return bool|never
         */
        return static function (int $errno, string $errstr, string $errfile = '', int $errline = 0): bool {
            if (! (error_reporting() & $errno)) {
                // error_reporting does not include this error
                return true;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
    }

    /**
     * Trigger all error listeners.
     */
    private function triggerListeners(
        Throwable $error,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void {
        foreach ($this->listeners as $listener) {
            $listener($error, $request, $response);
        }
    }
}
