<?php declare(strict_types=1);

namespace App\Resources\Factories;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\DbalMigrationFactory;
use Doctrine\Migrations\Version\MigrationFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MigrationFactoryDecorator
 *
 * From: https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html#migration-dependencies
 *
 * @package    App\Resources\Factories
 * @subpackage App\Resources\Factories\MigrationFactoryDecorator
 */
class MigrationFactoryDecorator implements MigrationFactory
{
    private MigrationFactory $factory;
    private ContainerInterface $container;

    public function __construct(DbalMigrationFactory $migrationFactory, ContainerInterface $container)
    {
        $this->factory   = $migrationFactory;
        $this->container = $container;
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = $this->factory->createVersion($migrationClassName);

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}
