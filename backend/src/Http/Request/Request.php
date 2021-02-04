<?php

namespace TestingTimes\Http\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TestingTimes\Http\Traits\ServerRequestDecoratorTrait;

/**
 * Class Request
 *
 * @package TestingTimes\Http\Request
 */
class Request implements RequestInterface
{
    use ServerRequestDecoratorTrait;

    /**
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI
     * @param array $headers Request headers
     * @param string|null|resource|StreamInterface $body Request body
     * @param string $version Protocol version
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1')
    {
        $this->message = new \Nyholm\Psr7\Request($method, $uri, $headers, $body, $version);
    }
}
