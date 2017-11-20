<?php

namespace Tranchard\CronMonitorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tranchard\CronMonitorBundle\DependencyInjection\Compiler\MonologPass;

class TranchardCronMonitorBundle extends Bundle
{

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MonologPass());
        parent::build($container);
    }
}
