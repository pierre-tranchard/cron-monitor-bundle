<?php

namespace Tranchard\CronMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TranchardCronMonitorExtension extends Extension
{

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->storeConfiguration($container, $config);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('transports.xml');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function storeConfiguration(ContainerBuilder $container, array $config)
    {
        foreach ($config as $key => $value) {
            $container->setParameter(sprintf('%s.%s', Configuration::getName(), $key), $value);
        }
    }
}
