<?php

namespace TestingTimes\Persistence\Command;

use HanWoolderink88\Container\Container;
use HanWoolderink88\Container\Model\ServiceInfo;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony/console command
 *
 * Class DebugContainerCommand
 * @package TestingTimes\Persistence\Command
 */
class DebugContainerCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'container:debug';

    /**
     * @var ContainerInterface
     */
    protected Container $container;

    public function __construct(Container $container, string $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->addArgument('find', InputArgument::OPTIONAL, 'full or partial name of the class');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $property = new \ReflectionProperty(get_class($this->container), "services");
        $property->setAccessible(true);

        /** @var ServiceInfo[] $services */
        $services = $property->getValue($this->container);

        $table = new Table($output);
        $table->setHeaders(['name', 'aliases', 'isObject', 'priority']);

        $find = $input->getArgument('find');

        if ($find) {
            $toPrint = [];
            foreach ($services as $service) {
                if (stripos($service->getName(), $find) !== false) {
                    $toPrint[] = $service;
                } else {
                    foreach ($service->getAliases() as $alias) {
                        if (stripos($alias, $find) !== false) {
                            $toPrint[] = $service;
                        }
                    }
                }
            }
        } else {
            $toPrint = $services;
        }

        foreach ($toPrint as $service) {
            $table->addRow([
                $service->getName(),
                implode("\n", [...$service->getAliases(), ' ', ' ']),
                $service->getService() !== null ? 'true' : 'false',
                $service->getPriority(),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
