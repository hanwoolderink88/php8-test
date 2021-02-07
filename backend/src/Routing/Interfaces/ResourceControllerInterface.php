<?php
declare(strict_types=1);

namespace TestingTimes\Routing\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ResourceControllerInterface
{
    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface;

    /**
     * @return ResponseInterface
     */
    public function post(): ResponseInterface;

    /**
     * @param  string  $id
     * @return ResponseInterface
     */
    public function get(string $id): ResponseInterface;

    /**
     * @param  string  $id
     * @return ResponseInterface
     */
    public function update(string $id): ResponseInterface;

    /**
     * @param  string  $id
     * @return ResponseInterface
     */
    public function delete(string $id): ResponseInterface;
}
