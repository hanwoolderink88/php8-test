<?php

namespace TestingTimes\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use TestingTimes\Http\Traits\ResponseDecoratorTrait;

/**
 * Class Response
 *
 * @package TestingTimes\Http\Response
 */
class Response implements ResponseInterface
{
    use ResponseDecoratorTrait;

    public function __construct(
        StreamInterface|string|null $body,
        int $status = 200,
        array $headers = [],
        $version = '1.1',
        ?string $reason = null
    ) {
        $this->message = new \Nyholm\Psr7\Response($status, $headers, $body, $version, $reason);
    }
}
