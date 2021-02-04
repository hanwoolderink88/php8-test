<?php

namespace TestingTimes\Http\Response;

use JsonException;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use TestingTimes\Http\Traits\ResponseDecoratorTrait;

/**
 * Class Response
 *
 * @package TestingTimes\Http\Response
 */
class JsonResponse implements ResponseInterface
{
    use ResponseDecoratorTrait;

    /**
     * @param string|array|object $body
     * @param int $status
     * @param array|string[] $headers
     * @param string $version
     * @param string|null $reason
     * @throws JsonException
     */
    public function __construct(
        string|array|object $body,
        int $status = 200,
        array $headers = ['Content-Type' => 'application/json'],
        $version = '1.1',
        ?string $reason = null
    ) {
        // add a header if not defined
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        if ($body instanceof JsonSerializable) {
            $body = json_encode($body->jsonSerialize());
        }

        if (is_object($body)) {
            $body = (array) $body;
        }

        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $this->message = new \Nyholm\Psr7\Response($status, $headers, $body, $version, $reason);
    }
}
