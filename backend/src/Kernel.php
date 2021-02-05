<?php

namespace TestingTimes;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use HanWoolderink88\Container\Container;
use HanWoolderink88\Container\Exception\ContainerAddServiceException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use TestingTimes\App\Controllers\IndexController;
use TestingTimes\App\Controllers\OrderController;
use TestingTimes\App\Controllers\PostController;
use TestingTimes\App\Controllers\ProductController;
use TestingTimes\App\Controllers\UserController;
use TestingTimes\Config\Config;
use TestingTimes\Config\Env;
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
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function bootstrap()
    {
        $cachePool = $this->loadCache();
        $this->loadDotEnv();

        // todo: when do you invalid this cache ?
        $env = $_SERVER['APP_ENV'] ?? 'production';
        if ($env !== 'local' && $cachePool->hasItem('bootstrap')) {
            $container = $cachePool->get('bootstrap');
        } else {
            $container = new Container();

            $router = $this->loadRouter();
            $config = $this->loadConfig();

            $env = $this->loadEnv();
            $entityManager = $this->loadPersistenceLayer($env, $config);

            $routeMatcher = new RouteMatcher($router);
            $routeMatcher->setContainer($container);

            $container->addService($config);
            $container->addService($env);
            $container->addService($entityManager);
            $container->addService($routeMatcher, null, true);

            // todo: add all classes in src to the container as a ref with $container->addServiceReference()

            $cachePool->save($cachePool->getItem('bootstrap')->set($container));
        }

        $container->addService($cachePool);

        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->addService($request, [RequestContract::class]);

        $routeMatcher = $this->container->get(RouteMatcher::class);

        return $routeMatcher->handle($request);
    }

    /**
     * @return void
     */
    protected function loadDotEnv(): void
    {
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();
        }
    }

    /**
     * @return Router
     * @throws Routing\Exceptions\RouterAddRouteException
     * @throws ReflectionException
     */
    protected function loadRouter(): Router
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

    /**
     * @return Config
     */
    protected function loadConfig(): Config
    {
        return new Config();
    }

    /**
     * @return Env
     */
    protected function LoadEnv(): Env
    {
        return new Env();
    }

    /**
     * @param Env $env
     * @param Config $config
     * @return EntityManager
     * @throws ORMException
     */
    protected function loadPersistenceLayer(Env $env, Config $config): EntityManager
    {
        switch (strtolower($config->get('cache.driver'))) {
            case 'apcu':
                $cache = new ApcuCache();
                break;
            default:
                $cache = null;
        }

        $emConfig = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . "/App/Entities"],
            $env->get('DOCTRINE_DEV_MODE', false),
            $env->get('DOCTRINE_PROXY_DIR'),
            $cache,
            $env->get('DOCTRINE_USE_SIMPLE_ANNOTATION_READER', false)
        );

        $connection = ['url' => $env->get('DOCTRINE_URL')];

        return EntityManager::create($connection, $emConfig);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return AbstractCachePool
     */
    private function loadCache(): AbstractCachePool
    {
        $fileLocation = dirname(__DIR__) . '/config/cache.php';
        if (is_file($fileLocation)) {
            $config = include $fileLocation;
            $driver = $config['driver'] ?? 'filesystem';
        } else {
            $driver = 'filesystem';
        }

        switch ($driver) {
            case 'apcu':
                return new ApcuCachePool();
            case 'filesystem':
            default:
                $filesystemAdapter = new Local(dirname(__DIR__) . '/storage/');
                $filesystem = new Filesystem($filesystemAdapter);

                return new FilesystemCachePool($filesystem);
        }
    }
}
