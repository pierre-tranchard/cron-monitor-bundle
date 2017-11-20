<?php

namespace Tranchard\CronMonitorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tranchard\CronMonitorBundle\DependencyInjection\Compiler\MonologPass;
use Tranchard\CronMonitorBundle\DependencyInjection\Compiler\TransportPass;

class TranchardCronMonitorBundle extends Bundle
{

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MonologPass());
        $container->addCompilerPass(new TransportPass());
        parent::build($container);
    }
}
