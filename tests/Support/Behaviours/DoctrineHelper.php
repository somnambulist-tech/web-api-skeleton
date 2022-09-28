<?php declare(strict_types=1);

namespace App\Tests\Support\Behaviours;

use Doctrine\ORM\EntityManagerInterface;
use Somnambulist\Components\Doctrine\AbstractModelLocator;

trait DoctrineHelper
{
    protected function doctrine(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    protected function locatorFor(string $class): AbstractModelLocator
    {
        return static::getContainer()->get('doctrine')->getManager()->getRepository($class);
    }
}
