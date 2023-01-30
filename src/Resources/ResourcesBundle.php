<?php declare(strict_types=1);

namespace App\Resources;

use Somnambulist\Components\Doctrine\TypeBootstrapper;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ResourcesBundle extends Bundle
{
    public function boot()
    {
        $this->registerDoctrineTypesAndEnumerations();
    }

    private function registerDoctrineTypesAndEnumerations()
    {
        TypeBootstrapper::registerEnumerations();
        TypeBootstrapper::registerTypes(TypeBootstrapper::$types);
    }
}
