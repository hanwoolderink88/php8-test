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
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @return mixed
     */
    public function post(string $key): mixed;

    /**
     * @param string $key
     * @return mixed
     */
    public function query(string $key): mixed;

    /**
     * @param string $key
     * @return mixed
     */
    public function header(string $key): mixed;
}
