<?php
declare(strict_types=1);

/** @var \TestingTimes\Config\Env $env */

$driver = $env->get('DATABASE_DRIVER');
$host = $env->get('DATABASE_HOST');
$port = $env->get('DATABASE_PORT');
$database = $env->get('DATABASE_NAME');
$user = $env->get('DATABASE_USER');
$password = $env->get('DATABASE_PASSWORD');

return [
    'url' => "{$driver}://{$user}:{$password}@{$host}:{$port}/{$database}",
    'dev_mode' => true,
    'use_simple_annotation_reader' => false,
    'proxy_dir' => null,
];
