<?php

namespace Tranchard\CronMonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MonologPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('monolog.logger')->setPublic(true);
        $container->getAlias('logger')->setPublic(true);
    }
}
