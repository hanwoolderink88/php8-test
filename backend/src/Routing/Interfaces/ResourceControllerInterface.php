<?php
declare(strict_types=1);

namespace TestingTimes\Routing\Interfaces;

use Nyholm\Psr7\Response;

interface ResourceControllerInterface
{
    /**
     * @return Response
     */
    public function index(): Response;

    /**
     * @return Response
     */
    public function post(): Response;

    /**
     * @return Response
     */
    public function get(string $id): Response;

    /**
     * @return Response
     */
    public function update(string $id): Response;

    /**
     * @return Response
     */
    public function delete(string $id): Response;
}
