<?php
declare(strict_types=1);

namespace TestingTimes\Http\Request;

use JetBrains\PhpStorm\Pure;
use JmesPath\Env;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Traits\ServerRequestDecoratorTrait;

/**
 * Decorator for Nyholm\Psr7\ServerRequest
 *
 * @package TestingTimes\Http\Request
 */
class Request implements RequestInterface, RequestContract
{
    use ServerRequestDecoratorTrait;

    /**
     * @param  string  $method  HTTP method
     * @param  string|UriInterface  $uri  URI
     * @param  array  $headers  Request headers
     * @param  string|null|resource|StreamInterface  $body  Request body
     * @param  string  $version  Protocol version
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1')
    {
        $this->validateBody($body, $headers);

        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }

        $message = new ServerRequest($method, $uri, $headers, $body, $version);
        $messageWithQuery = $message->withQueryParams($this->parseQueryParamsToArray($uri->getQuery()));

        $this->message = $messageWithQuery;
    }

    /**
     * @param  StreamInterface|string|null  $body
     * @param  array  $headers
     * @return StreamInterface|string|null
     */
    protected function validateBody(StreamInterface|string|null $body, array $headers)
    {
        if ($this->isJsonRequest($headers)) {
            if ($body instanceof StreamInterface) {
                $strBody = $body->__toString();
                json_decode($strBody, true, JSON_THROW_ON_ERROR);
            } else {
                json_decode($body, true, JSON_THROW_ON_ERROR);
            }
        }

        // todo: maybe xml etc parsing
        return $body;
    }

    /**
     * @param  array  $headers
     * @return bool
     */
    #[Pure] protected function isJsonRequest(array $headers): bool
    {
        $hasHeader = isset($headers['content-type']);
        if (!$hasHeader) {
            return false;
        }

        return (is_array($headers['content-type']) && in_array('application/json',
                    $headers['content-type'])) || (is_string($headers['content-type']) && $headers['content-type'] === 'application/json');
    }

    /**
     * @param  string  $queryString
     * @return mixed
     */
    #[Pure] protected function parseQueryParamsToArray(string $queryString): array
    {
        parse_str($queryString, $queryArray);

        return $queryArray;
    }

    /**
     * @param  string  $key
     * @param  null  $fallback
     * @return mixed
     */
    public function get(string $key, $fallback = null): mixed
    {
        $found = $this->post($key, $fallback = null);
        if ($found) {
            return $found;
        }

        $found = $this->query($key, $fallback = null);
        if ($found) {
            return $found;
        }

        $found = $this->header($key, $fallback = null);

        return $found;
    }

    /**
     * @param  string  $key
     * @param  null  $fallback
     * @return mixed
     */
    public function post(string $key, $fallback = null): mixed
    {
        if (!$this->isJsonRequest($this->getMessage()->getHeaders())) {
            return $fallback;
        }

        return Env::search($key, $this->getJsonBody());
    }

    /**
     * @return array
     */
    public function getJsonBody(): array
    {
        $body = $this->getMessage()->getBody()->__toString();

        return json_decode($body, true, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  string  $key
     * @param  null  $fallback
     * @return mixed
     */
    public function query(string $key, $fallback = null): mixed
    {
        return $this->getQueryParams()[$key] ?? $fallback;
    }

    /**
     * @param  string  $key
     * @param  null  $fallback
     * @return mixed
     */
    public function header(string $key, $fallback = null): mixed
    {
        return $this->getheaders()[$key] ?? $fallback;
    }
}
