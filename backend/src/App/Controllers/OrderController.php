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
class OrderController
{
    #[Route('api/orders', ['GET'])]
    public function index(): Response
    {
        return new Response(200, [], 'list of orders');
    }

    #[Route('api/orders', ['POST'])]
    public function post(): Response
    {
        return new Response(200, [], 'create order');
    }

    #[Route('api/orders/{id}', ['GET'])]
    public function get(
        string $id
    ): Response {
        return new Response(200, [], 'order details ' . $id);
    }

    #[Route('api/orders/{id}', ['PUT', 'PATCH'])]
    public function update(
        string $id
    ): Response {
        return new Response(200, [], 'update order ' . $id);
    }

    #[Route('api/orders/{id}', ['DELETE'])]
    public function delete(
        string $id
    ): Response {
        return new Response(200, [], 'delete order ' . $id);
    }
}
