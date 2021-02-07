<?php
declare(strict_types=1);

namespace TestingTimes\Http\Contracts;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestContract
 *
 * @package TestingTimes\Http\Contracts
 */
interface RequestContract extends ServerRequestInterface
{
    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function get(string $key, mixed $fallback = null): mixed;

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function post(string $key, mixed $fallback = null): mixed;

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function query(string $key, mixed $fallback = null): mixed;

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function header(string $key, mixed $fallback = null): mixed;
}
