<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use TestingTimes\App\Entities\User;
use TestingTimes\Config\Config;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Exceptions\HttpNotFoundException;
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
    public function index(EntityManager $entityManager): ResponseInterface
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        return new JsonResponse($users);
    }

    public function post(RequestContract $request, Config $config): ResponseInterface
    {
        $config = $config->get('FOO');
        $fooBar = $request->post('foo[*].bar');
        $hello = $request->query('hello');
        $type = $request->header('content-type');

        return new JsonResponse(get_defined_vars(), 201);
    }

    public function get(EntityManager $entityManager, string $id): ResponseInterface
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            throw new HttpNotFoundException();
        }

        return new JsonResponse($user);
    }

    public function update(string $id): ResponseInterface
    {
        return new Response("updated user with id {$id}", 200);
    }

    public function delete(string $id): ResponseInterface
    {
        return new Response("deleted user with id {$id}", 200);
    }
}
