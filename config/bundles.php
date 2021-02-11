<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true, 'docker' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],
    Liip\TestFixturesBundle\LiipTestFixturesBundle::class => ['dev' => true, 'test' => true, 'docker' => true],
    SamJ\FractalBundle\SamJFractalBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['dev' => true, 'test' => true, 'docker' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true, 'docker' => true],
    App\Resources\ResourcesBundle::class => ['all' => true],
    Somnambulist\Bundles\FormRequestBundle\SomnambulistFormRequestBundle::class => ['all' => true],
    Somnambulist\Bundles\ApiBundle\SomnambulistApiBundle::class => ['all' => true],
    Somnambulist\Bundles\ReadModelsBundle\SomnambulistReadModelsBundle::class => ['all' => true],
];
