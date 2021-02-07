<?php
declare(strict_types=1);

namespace TestingTimes\ErrorHandling;

use Illuminate\Support\Str;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use TestingTimes\Config\Env;
use TestingTimes\Http\Response\JsonResponse;
use Throwable;

/**
 * Class ErrorHandler
 *
 * @package TestingTimes\ErrorHandling
 */
class ErrorHandler
{
    /**
     * @var Env
     */
    protected Env $env;

    /**
     * @param  Env  $env
     */
    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    /**
     * @param  Throwable  $exception
     * @param  ResponseInterface|null  $response
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function handle(Throwable $exception, ?ResponseInterface $response = null): ResponseInterface
    {
        if ($response === null) {
            $response = new Response();
        }

        $statuscode = $exception->getCode() > 0 ? $exception->getCode() : 500;
        $body = [
            'message' => $exception->getMessage(),
        ];

        if (Str::lower($this->env->get('APP_ENV')) === 'local') {
            $body['file'] = $exception->getFile();
            $body['code'] = $exception->getCode();
            $body['line'] = $exception->getLine();
            $body['trace'] = $exception->getTrace();
        }

        if ($response instanceof JsonResponse) {
            /** @var JsonResponse $response */
            return $response->withBody($body)->withStatus($statuscode);
        }

        // todo: this can be a bit better i think...
        return $response->withBody(Stream::create($exception->__toString()))->withStatus($statuscode);
    }
}
