<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use TestingTimes\App\Entities\User;
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

    public function post(EntityManager $entityManager, RequestContract $request): ResponseInterface
    {
        $name = $request->post('name');

        $user = new User();
        $user->setName($name);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse($user, 201);
    }

    public function get(EntityManager $entityManager, string $id): ResponseInterface
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            throw new HttpNotFoundException('user not found');
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
