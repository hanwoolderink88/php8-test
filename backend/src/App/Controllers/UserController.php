<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Response\JsonResponse;
use TestingTimes\Http\Response\Response;
use TestingTimes\Routing\Attributes\RouteResource;

/**
 * Class OrderController
 *
 * @package App\Controllers
 */
#[RouteResource('api/users')]
class UserController
{
    public function index(): ResponseInterface
    {
        $body = [];
        for ($i = 0; $i < 20; $i++) {
            $body[] = (fn() => $this->testUser($i))();
        }

        return new JsonResponse($body);
    }

    public function post(RequestContract $request): ResponseInterface
    {
        $fooBar = $request->post('foo[*].bar');
        $hello = $request->query('hello');
        $type = $request->header('content-type');

        return new JsonResponse($fooBar, 201);
    }

    public function get(string $id): ResponseInterface
    {
        return new JsonResponse($this->testUser($id));
    }

    public function update(string $id): ResponseInterface
    {
        return new Response("updated user with id {$id}", 200);
    }

    public function delete(string $id): ResponseInterface
    {
        return new Response("deleted user with id {$id}", 200);
    }

    #[Pure] private function testUser($id)
    {
        $user = new stdClass();
        $user->id = (int) $id;
        $user->name = 'John Doe';

        return $user;
    }
}
