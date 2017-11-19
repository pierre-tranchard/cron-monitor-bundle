<?php

namespace Tranchard\CronMonitorBundle\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Tranchard\CronMonitorBundle\DependencyInjection\Configuration;
use Tranchard\CronMonitorBundle\Model\CronReporter;
use Symfony\Component\HttpFoundation\Request as RequestFoundation;

trait CronMonitor
{

    use ContainerAwareTrait;

    /**
     * Mandatory to get the command name
     *
     * @inheritdoc
     */
    abstract public function getName();

    /**
     * Mandatory to get the command description
     *
     * @inheritdoc
     */
    abstract public function getDescription();

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var null|Client
     */
    private static $client = null;

    /**
     * @var null|SerializerInterface
     */
    private static $serializer = null;

    /**
     * @var array
     */
    private static $configuration;

    /**
     * @var CronReporter
     */
    private $reporter;

    /**
     * @var Stopwatch
     */
    private $stopWatch;

    /**
     * @param array $extraInformation
     *
     * @return CronReporter|null
     */
    public function start(array $extraInformation = [])
    {
        $bundleAlias = Configuration::getName();
        $this->enabled = (bool)$this->container->getParameter(sprintf('%s.enabled', $bundleAlias));
        if ($this->enabled === false) {
            return null;
        }
        $this->initializeReporter($bundleAlias);
        $this->stopWatch->start('cron');
        $project = $this->container->getParameter(sprintf('%s.project', $bundleAlias));
        $this->reporter = new CronReporter(
            $project['name'],
            $this->getName(),
            $this->container->getParameter('kernel.environment'),
            $this->getDescription()
        );
        $this->reporter->addExtraPayload($extraInformation);
        $this->callApi();

        return $this->reporter;
    }

    /**
     * @param string $status
     * @param array  $extraInformation
     *
     * @return CronReporter|null
     */
    public function end(string $status = CronReporter::STATUS_SUCCESS, array $extraInformation = [])
    {
        if ($this->enabled === false) {
            return null;
        }
        if (!$this->initialized) {
            throw new \RuntimeException('You must call start first');
        }
        if (!$this->stopWatch->isStarted('cron')) {
            return $this->reporter;
        }
        $event = $this->stopWatch->stop('cron');
        $this->reporter->setDuration($event->getDuration() * 1000);
        $this->reporter->setStatus($status);
        $this->reporter->addExtraPayload(array_merge(['memory_usage' => $event->getMemory()], $extraInformation));
        $this->callApi();

        return $this->reporter;
    }

    /**
     * @param array $extraInformation
     *
     * @return null|CronReporter
     * @throws \RuntimeException
     */
    public function failure(array $extraInformation = [])
    {
        if ($this->enabled === false) {
            return null;
        }
        if (!$this->initialized) {
            throw new \RuntimeException('You must call start first');
        }
        if (!$this->stopWatch->isStarted('cron')) {
            return $this->reporter;
        }
        $event = $this->stopWatch->stop('cron');
        $this->reporter->setDuration($event->getDuration() * 1000);
        $this->reporter->setStatus(CronReporter::STATUS_FAILED);
        $this->reporter->addExtraPayload(array_merge(['memory_usage' => $event->getMemory()], $extraInformation));
        $this->callApi();

        return $this->reporter;
    }

    /**
     * @return CronReporter|null
     * @throws \RuntimeException
     */
    public function getReporter()
    {
        if ($this->enabled === false) {
            return null;
        }
        if (is_null($this->reporter)) {
            throw new \RuntimeException('You must call start method');
        }

        return $this->reporter;
    }

    /**
     * Call API
     */
    private function callApi()
    {
        if (is_null($this->reporter->getId())) {
            $verb = RequestFoundation::METHOD_POST;
            $route = sprintf('/%s/%s/cron-reporter', self::$configuration['path'], self::$configuration['version']);
        } else {
            $verb = RequestFoundation::METHOD_PUT;
            $route = sprintf(
                '/%s/%s/cron-reporter/%s',
                self::$configuration['path'],
                self::$configuration['version'],
                $this->reporter->getId()
            );
        }
        $body = self::$serializer->serialize(
            ['tranchard_cron_monitor_api_form_type_cron_reporter' => $this->reporter],
            'json',
            ['groups' => ['create']]
        );
        $request = new Request($verb, $route, ['Content-Type' => 'application/json'], $body);
        try {
            $promise = self::$client->sendAsync($request);
            $promise->then(
                function (ResponseInterface $response) {
                    try {
                        $this->reporter = self::$serializer->deserialize(
                            $response->getBody(),
                            CronReporter::class,
                            'json',
                            ['groups' => ['display']]
                        );
                    } catch (\Exception $exception) {
                        $this->container->get('logger')->critical($exception->getMessage(), ['caller' => __CLASS__]);
                    }
                },
                function (RequestException $exception) {
                    $this->container->get('logger')->critical($exception->getMessage(), ['caller' => __CLASS__]);
                }
            );
            $promise->wait();
        } catch (\Exception $exception) {
            $logger = $this->container->get('logger');
            $logger->error($exception->getMessage(), ['caller' => __CLASS__]);
            $transport = $this->container->getParameter('allo_cine_cron_reporter.fallback')['transport'];
            $this->reporter->addExtraPayload(
                [
                    'trace'   => $exception->getTraceAsString(),
                    'message' => $exception->getMessage(),
                ]
            );
            $this->container->get('tranchard_cron_monitor.transports.transport_factory')
                            ->get($transport)
                            ->send(
                                $this->reporter,
                                function (CronReporter $cronReporter) {
                                    $cronReporter->addExtraPayload(['trace' => null, 'message' => null]);
                                },
                                function (CronReporter $cronReporter) use ($logger) {
                                    $logger->critical(
                                        sprintf(
                                            'Fail to fallback for %s %s %s',
                                            $cronReporter->getProject(),
                                            $cronReporter->getEnvironment(),
                                            $cronReporter->getJob()
                                        )
                                    );
                                }
                            );
        }
    }

    /**
     * @param string $bundleAlias
     */
    private function initializeReporter(string $bundleAlias)
    {
        if (is_null(self::$client) || is_null(self::$serializer)) {
            self::$configuration = $this->container->getParameter(sprintf('%s.api', $bundleAlias));
            self::$client = new Client(
                [
                    'base_uri' => self::$configuration['host'],
                    'timeout'  => self::$configuration['timeout'],
                ]
            );
            self::$serializer = $this->container->get('serializer');
            $this->stopWatch = new Stopwatch();
            $this->initialized = true;
        }
    }
}
