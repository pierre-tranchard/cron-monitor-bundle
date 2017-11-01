<?php

namespace Tranchard\CronMonitorBundle\Transports\Drivers;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tranchard\CronMonitorBundle\Transports\TransportInterface;

abstract class AbstractTransport implements TransportInterface
{

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @inheritdoc
     */
    public function setConfiguration(array $configuration): TransportInterface
    {
        $resolver = $this->configureOptions(new OptionsResolver());
        $this->configuration = $resolver->resolve($configuration);

        return $this;
    }

    /**
     * @inheritdoc
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        return $resolver
            ->setRequired(['transport', 'target',])
            ->setAllowedTypes('transport', 'string')
            ->setAllowedTypes('target', 'string');
    }
}
