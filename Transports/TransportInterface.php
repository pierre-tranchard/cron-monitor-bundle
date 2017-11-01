<?php

namespace Tranchard\CronMonitorBundle\Transports;

use Tranchard\CronMonitorBundle\Exception\TransportException;
use Tranchard\CronMonitorBundle\Model\CronReporter;

interface TransportInterface
{

    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @param CronReporter  $cronReporter
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     *
     * @return bool
     * @throws TransportException
     */
    public function send(CronReporter $cronReporter, callable $onSuccess = null, callable $onFailure = null): bool;

    /**
     * @param array $configuration
     *
     * @return TransportInterface
     */
    public function setConfiguration(array $configuration): TransportInterface;
}
