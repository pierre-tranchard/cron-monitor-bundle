<?php

namespace Tranchard\CronMonitorBundle\Transports;

use Tranchard\CronMonitorBundle\Exception\TransportException;

class TransportFactory
{

    /**
     * @var TransportInterface[]
     */
    private $transports = [];

    /**
     * TransportFactory constructor.
     *
     * @param iterable $transports
     */
    public function __construct(iterable $transports)
    {
        foreach ($transports as $transport) {
            /** @var $transport TransportInterface */
            $this->transports[$transport::getName()] = $transport;
        }
    }

    /**
     * @param string $name
     *
     * @return TransportInterface
     * @throws TransportException
     */
    public function get(string $name): TransportInterface
    {
        if (!isset($this->transports[$name])) {
            throw TransportException::notFoundException(sprintf('Transport "%s" not found', $name));
        }

        return $this->transports[$name];
    }
}
