<?php

namespace TestingTimes;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use HanWoolderink88\Container\Container;
use HanWoolderink88\Container\Exception\ContainerAddServiceException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TestingTimes\App\Controllers\IndexController;
use TestingTimes\App\Controllers\OrderController;
use TestingTimes\App\Controllers\PostController;
use TestingTimes\App\Controllers\ProductController;
use TestingTimes\App\Controllers\UserController;
use TestingTimes\Config\Config;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Routing\RouteMatcher;
use TestingTimes\Routing\RouteParser;
use TestingTimes\Routing\Router;

/**
 * Class Kernel
 *
 * @package TestingTimes
 */
class Kernel implements RequestHandlerInterface
{
    protected ContainerInterface $container;

    /**
     * Should go to its own class
     *
     * @throws ContainerAddServiceException
     */
    public function bootstrap()
    {
        $container = new Container();

        $this->loadDotEnv();
        $router = $this->loadRouter();
        $config = $this->loadConfig();
        $entityManager = $this->loadPersistenceLayer($config);
        $routeMatcher = new RouteMatcher($router);
        $routeMatcher->setContainer($container);

        $container->addService($config);
        $container->addService($entityManager);
        $container->addService($routeMatcher, null, true);

        // todo: add all classes in src to the container as a ref with $container->addServiceReference()
        // todo: maybe cache after this but how to know if any changes

        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->addService($request, [RequestContract::class]);

        $routeMatcher = $this->container->get(RouteMatcher::class);

        return $routeMatcher->handle($request);
    }

    protected function loadDotEnv()
    {
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();
        }
    }

    protected function loadRouter()
    {
        // Create router and add routes.. this can be cached..
        $router = new Router();
        $router->flushRoutes();
        $routeParser = new RouteParser($router);
        $routeParser
            ->byClassMethods(IndexController::class)
            ->byClassMethods(OrderController::class)
            ->byResource(UserController::class)
            ->byClassMethods(PostController::class)
            ->byClassMethods(ProductController::class);

        return $router;
    }

    protected function loadConfig()
    {
        return new Config();
    }

    protected function loadPersistenceLayer($config)
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $cache = null;
        $emConfig = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . "/App/Entities"],
            $config->get('DOCTRINE_DEV_MODE', false),
            $config->get('DOCTRINE_PROXY_DIR', null),
            $cache,
            $config->get('DOCTRINE_USE_SIMPLE_ANNOTATION_READER', false)
        );

        $connection = [
            'url' => $config->get('DOCTRINE_URL', null),
        ];

        // obtaining the entity manager
        return EntityManager::create($connection, $emConfig);
    }
}
