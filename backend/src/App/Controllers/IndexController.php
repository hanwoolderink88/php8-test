<?php
declare(strict_types=1);

namespace TestingTimes\App\Controllers;

use Nyholm\Psr7\Response;
use TestingTimes\Routing\Attributes\Route;

class IndexController
{
    #[Route('/api', ['GET'])]
    public function index(): Response
    {
        return new Response(200, [], 'homepage');
    }
}
