<?php

namespace Tranchard\CronMonitorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tranchard\CronMonitorBundle\Transports\Drivers\Filesystem;
use Tranchard\CronMonitorBundle\Transports\Drivers\Mailer;

class Configuration implements ConfigurationInterface
{

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::getName());
        $rootNode
            ->children()
                ->scalarNode('enabled')
                    ->info('default true, allow to disable it depending on the environment')
                    ->defaultTrue()
                ->end()
                ->arrayNode('project')
                    ->children()
                        ->scalarNode('name')
                        ->info('the project name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('api')
                ->children()
                    ->scalarNode('host')
                        ->info('the API host with the scheme, default value http://api.cron-monitor.localhost')
                        ->example('http://api.cron-monitor.localhost')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('path')
                        ->info("the path to reach the API, default api")
                        ->example('api')
                        ->cannotBeEmpty()
                        ->defaultValue('api')
                    ->end()
                    ->scalarNode('version')
                        ->info("the API version, default v1")
                        ->example('v1')
                        ->cannotBeEmpty()
                        ->defaultValue('v1')
                    ->end()
                    ->scalarNode('secret')
                        ->info('if your calls are signed, you can declared the key here, default null')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('timeout')
                        ->info('the timeout for the API calls, default 2.0')
                        ->example('2.0')
                        ->defaultValue(2.0)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('fallback')
                ->children()
                    ->enumNode('transport')
                    ->values([Filesystem::getName(), Mailer::getName()])
                    ->defaultValue(Filesystem::getName())
                    ->info('Fallback transport in case the first attempt fails')
                ->end()
                ->scalarNode('target')
                    ->defaultValue('%kernel.logs_dir%/cron-monitor')
                    ->info('Folder, email or channel for the notification')
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    public static function getName(): string
    {
        return 'tranchard_cron_monitor';
    }
}
