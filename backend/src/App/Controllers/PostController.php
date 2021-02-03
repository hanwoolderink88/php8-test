<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use Nyholm\Psr7\Response;
use TestingTimes\Routing\Attributes\Route;

/**
 * Class OrderController
 *
 * @package App\Controllers
 */
class PostController
{
    #[Route('api/posts', ['GET'])]
    public function index(): Response
    {
        return new Response(200, [], 'list of posts');
    }

    #[Route('api/posts/id', ['GET'])]
    public function get(): Response
    {
        return new Response(200, [], 'post details');
    }

    #[Route('api/posts/id', ['POST'])]
    public function post(): Response
    {
        return new Response(200, [], 'create post');
    }

    #[Route('api/posts/id', ['PUT', 'PATCH'])]
    public function update(): Response
    {
        return new Response(200, [], 'update post');
    }

    #[Route('api/posts/id', ['DELETE'])]
    public function delete(): Response
    {
        return new Response(200, [], 'delete post');
    }
}
