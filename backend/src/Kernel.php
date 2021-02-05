<?php
declare(strict_types=1);

namespace TestingTimes;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use HanWoolderink88\Container\Container;
use HanWoolderink88\Container\Exception\ContainerAddServiceException;
use JsonException;
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
use TestingTimes\App\Repository\UserRepository;
use TestingTimes\Config\Config;
use TestingTimes\Config\Env;
use TestingTimes\ErrorHandling\ErrorHandler;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Response\JsonResponse;
use TestingTimes\Routing\RouteMatcher;
use TestingTimes\Routing\RouteParser;
use TestingTimes\Routing\Router;
use Throwable;

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

        $useCache = $_SERVER['APP_CACHE_ENABLED'] === 'true';
        if ($useCache && $cachePool->hasItem('bootstrap')) {
            $container = $cachePool->get('bootstrap');
        } else {
            $container = new Container();

            $env = $this->loadEnv();
            $config = $this->loadConfig($env);
            $router = $this->loadRouter();

            $entityManager = $this->loadPersistenceLayer($env, $config);

            $routeMatcher = new RouteMatcher($router);
            $routeMatcher->setContainer($container);

            $container->addService($config);
            $container->addService($env);
            $container->addService($entityManager);
            $container->addService($routeMatcher);
            $container->addServiceReference(ErrorHandler::class);

            $container->sortIndex();

            // todo: add all classes in src to the container as a ref with $container->addServiceReference()
            $container->addServiceReference(UserRepository::class);

            if ($useCache) {
                $cachePool->save($cachePool->getItem('bootstrap')->set($container));
            }
        }

        $container->addService($cachePool);

        $this->container = $container;
    }

    /**
     * @param  ServerRequestInterface  $request
     * @return ResponseInterface
     * @throws JsonException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->addService($request, [RequestContract::class]);

        try {
            $routeMatcher = $this->container->get(RouteMatcher::class);

            return $routeMatcher->handle($request);
        } catch (Throwable $throwable) {
            /** @var ErrorHandler $handler */
            $handler = $this->container->get(ErrorHandler::class);

            // todo: second param should be dynamic based on config i guess...
            return $handler->handle($throwable, new JsonResponse(''));
        }
    }

    /**
     * @return void
     */
    protected function loadDotEnv(): void
    {
        if (file_exists(dirname(__DIR__).'/.env')) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();
        }
    }

    /**
     * Todo: this should be a file looup based on defined dirs in config a config file
     *
     * @return Router
     * @throws Routing\Exceptions\RouterAddRouteException
     * @throws ReflectionException
     */
    protected function loadRouter(): Router
    {
        $router = new Router();
        $router->flushRoutes();
        $routeParser = new RouteParser($router);
        $routeParser->byClassMethods(IndexController::class)
            ->byClassMethods(OrderController::class)
            ->byResource(UserController::class)
            ->byClassMethods(PostController::class)
            ->byClassMethods(ProductController::class);

        return $router;
    }

    /**
     * @return Config
     */
    protected function loadConfig(Env $env): Config
    {
        return new Config($env);
    }

    /**
     * @return Env
     */
    protected function LoadEnv(): Env
    {
        return new Env();
    }

    /**
     * @param  Env  $env
     * @param  Config  $config
     * @return EntityManager
     * @throws ORMException
     */
    protected function loadPersistenceLayer(Env $env, Config $config): EntityManager
    {
        if ($env->get('APP_CACHE_ENABLED')) {
            $cache = match (strtolower($config->get('cache.driver'))) {
                'apcu' => new ApcuCache(),
                default => new FilesystemCache(dirname(__DIR__).'/storage/cache'),
            };
        } else {
            $cache = null;
        }

        $emConfig = Setup::createAnnotationMetadataConfiguration([__DIR__."/App/Entities"],
            $config->get('doctrine.dev_mode'),
            $config->get('doctrine.proxy_dir'),
            $cache,
            $config->get('doctrine.use_simple_annotation_reader'));

        $connection = ['url' => $config->get('doctrine.url')];

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
        $fileLocation = dirname(__DIR__).'/config/cache.php';
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
                $filesystemAdapter = new Local(dirname(__DIR__).'/storage/');
                $filesystem = new Filesystem($filesystemAdapter);

                return new FilesystemCachePool($filesystem);
        }
    }
}
