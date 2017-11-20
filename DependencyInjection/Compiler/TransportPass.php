<?php

namespace Tranchard\CronMonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TransportPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('tranchard_cron_monitor.fallback')) {
            return;
        }

        $config = $container->getParameter('tranchard_cron_monitor.fallback');
        $taggedServices = $container->findTaggedServiceIds('tranchard.cron_monitor.transport');
        foreach ($taggedServices as $id => $tags) {
            $definition = $container->getDefinition($id)->addMethodCall('setConfiguration', [$config]);
            $container->setDefinition($id, $definition);
        }

    }
}
