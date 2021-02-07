<?php
declare(strict_types=1);

namespace TestingTimes\Http\Response;

use JsonException;
use JsonSerializable;
use Nyholm\Psr7\Stream;
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
     * @param  mixed  $body
     * @param  int  $status
     * @param  array|string[]  $headers
     * @param  string  $version
     * @param  string|null  $reason
     * @throws JsonException
     */
    public function __construct(
        mixed $body,
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
            $body = (array)$body;
        }

        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $this->message = new \Nyholm\Psr7\Response($status, $headers, $body, $version, $reason);
    }

    /**
     * @param  mixed  $body
     * @return $this
     * @throws JsonException
     */
    public function withBody($body): self
    {
        if ($body instanceof JsonSerializable) {
            $body = json_encode($body->jsonSerialize());
        }

        if (is_object($body)) {
            $body = (array)$body;
        }

        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $new = clone $this;
        $new->message = $this->message->withBody(Stream::create($body));

        return $new;
    }
}
