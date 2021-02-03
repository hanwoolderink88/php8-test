<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use Nyholm\Psr7\Response;
use TestingTimes\Routing\Attributes\RouteResource;
use TestingTimes\Routing\Interfaces\ResourceControllerInterface;

/**
 * Class OrderController
 *
 * @package App\Controllers
 */
#[RouteResource('api/users')]
class UserController implements ResourceControllerInterface
{
    public function index(): Response
    {
        return new Response(200, [], 'list of users');
    }

    public function post(): Response
    {
        return new Response(200, [], 'user details');
    }

    public function get(string $id): Response
    {
        return new Response(200, [], 'create user ' . $id);
    }

    public function update(string $id): Response
    {
        return new Response(200, [], 'update user ' . $id);
    }

    public function delete(string $id): Response
    {
        return new Response(200, [], 'delete user ' . $id);
    }
}
