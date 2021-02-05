#!/usr/bin/env php
<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationFileWithFallback;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command\ClearCache\CollectionRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\EntityRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use TestingTimes\Kernel;

$kernel = new Kernel();
$kernel->bootstrap();
$container = $kernel->getContainer();

$application = new Application();

$entityManager = $container->get(EntityManager::class);

$helperSet = new HelperSet(
    [
        'db' => new ConnectionHelper($entityManager->getConnection()),
        'em' => new EntityManagerHelper($entityManager),
    ]
);

$application->setHelperSet($helperSet);

$dependencyFactory = DependencyFactory::fromEntityManager(
    new ConfigurationFileWithFallback('config/migrations.php'),
    new ExistingEntityManager($entityManager)
);

// ... register commands
$application->addCommands(
    [
        // DBAL Commands
        new Doctrine\DBAL\Tools\Console\Command\ImportCommand(),
        new ReservedWordsCommand(),
        new RunSqlCommand(),

        // ORM Commands
        new CollectionRegionCommand(),
        new EntityRegionCommand(),
        new MetadataCommand(),
        new QueryCommand(),
        new QueryRegionCommand(),
        new ResultCommand(),
        new CreateCommand(),
        new UpdateCommand(),
        new DropCommand(),
        new EnsureProductionSettingsCommand(),
        new ConvertDoctrine1SchemaCommand(),
        new GenerateRepositoriesCommand(),
        new GenerateEntitiesCommand(),
        new GenerateProxiesCommand(),
        new ConvertMappingCommand(),
        new RunDqlCommand(),
        new ValidateSchemaCommand(),
        new InfoCommand(),
        new MappingDescribeCommand(),

        // doctrine migrations
        new CurrentCommand($dependencyFactory),
        new DumpSchemaCommand($dependencyFactory),
        new ExecuteCommand($dependencyFactory),
        new GenerateCommand($dependencyFactory),
        new LatestCommand($dependencyFactory),
        new MigrateCommand($dependencyFactory),
        new RollupCommand($dependencyFactory),
        new StatusCommand($dependencyFactory),
        new VersionCommand($dependencyFactory),
        new UpToDateCommand($dependencyFactory),
        new SyncMetadataCommand($dependencyFactory),
        new ListCommand($dependencyFactory),
        new DiffCommand($dependencyFactory)
    ]
);
$application->run();