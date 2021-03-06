<?php
declare(strict_types=1);

/**
 * @package    Grav\Framework\Psr7
 * @copyright  Copyright (C) 2015 - 2020 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace TestingTimes\Http\Traits;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
trait ResponseDecoratorTrait
{
    use MessageDecoratorTrait {
        getMessage as private;
    }

    /**
     * Exchanges the underlying response with another.
     *
     * @param  ResponseInterface  $response
     * @return self
     */
    public function withResponse(ResponseInterface $response): static
    {
        $new = clone $this;
        $new->message = $response;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Returns the decorated response.
     * Since the underlying Response is immutable as well
     * exposing it is not an issue, because it's state cannot be altered
     *
     * @return ResponseInterface
     */
    #[Pure] public function getResponse(): ResponseInterface
    {
        /** @var ResponseInterface $message */
        $message = $this->getMessage();

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->message = $this->getResponse()->withStatus($code, $reasonPhrase);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->getResponse()->getReasonPhrase();
    }
}
