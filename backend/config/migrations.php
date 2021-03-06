<?php
declare(strict_types=1);

/** @var \TestingTimes\Config\Env $env */

// needed config file for doctrine-migrations

return [
    'table_storage' => [
        'table_name' => 'migrations',
        'version_column_name' => 'version',
        'version_column_length' => 1024,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],

    'migrations_paths' => [
        'TestingTimes\Migrations' => dirname(__DIR__) . '/migrations/' . $_SERVER['DATABASE_DRIVER'],
    ],

    'all_or_nothing' => true,
    'check_database_platform' => true,
    'organize_migrations' => 'none',
];
