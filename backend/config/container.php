<?php
declare(strict_types=1);

/** @var \TestingTimes\Config\Env $env */

return [
    'autowire' => [
        dirname(__DIR__).'/src/App',
        dirname(__DIR__).'/src/Config',
        dirname(__DIR__).'/src/ErrorHandling',
        dirname(__DIR__).'/src/Http',
        dirname(__DIR__).'/src/Persistence',
        dirname(__DIR__).'/src/Routing',
    ],
];
