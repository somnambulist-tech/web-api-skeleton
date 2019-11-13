<?php declare(strict_types=1);

namespace App\Resources;

use Somnambulist\Domain\Doctrine\Bootstrapper;
use Somnambulist\ReadModels\Model;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ResourcesBundle
 *
 * @package App\Resources
 * @subpackage App\Resources\ResourcesBundle
 */
class ResourcesBundle extends Bundle
{

    /**
     * @var bool
     */
    private static $booted = false;

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {

    }

    /**
     * Setup services on bundle boot
     */
    public function boot()
    {
        Request::setTrustedProxies(
            ['127.0.0.1', '192.168.0.0/16', '10.0.0.1/8', '172.0.0.1/8'],
            Request::HEADER_X_FORWARDED_ALL
        );

        $this->registerDoctrineTypesAndEnumerations();

        if (false === static::$booted) {
            Model::bindConnection($this->container->get('doctrine.dbal.default_connection'));
        }

        static::$booted = true;
    }

    private function registerDoctrineTypesAndEnumerations()
    {
        Bootstrapper::registerEnumerations();
        Bootstrapper::registerTypes();
        Bootstrapper::registerExtendedTypes();
    }
}
