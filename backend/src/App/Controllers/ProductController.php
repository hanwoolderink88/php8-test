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
class ProductController
{
    #[Route('api/products', ['GET'])]
    public function index(): Response
    {
        return new Response(200, [], 'list of products');
    }

    #[Route('api/products/id', ['GET'])]
    public function get(): Response
    {
        return new Response(200, [], 'product details');
    }

    #[Route('api/products/id', ['POST'])]
    public function post(): Response
    {
        return new Response(200, [], 'create product');
    }

    #[Route('api/products/id', ['PUT', 'PATCH'])]
    public function update(): Response
    {
        return new Response(200, [], 'update product');
    }

    #[Route('api/products/id', ['DELETE'])]
    public function delete(): Response
    {
        return new Response(200, [], 'delete product');
    }
}
