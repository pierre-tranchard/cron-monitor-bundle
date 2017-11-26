<?php

namespace Tranchard\CronMonitorBundle\EventListener;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tranchard\CronMonitorBundle\Component\Console\CronMonitorOutput;
use Tranchard\CronMonitorBundle\Component\Console\CronMonitorStreamOutput;
use Tranchard\CronMonitorBundle\Component\Console\SharedBufferCronMonitorOutput;
use Tranchard\CronMonitorBundle\Model\CronReporter;
use Tranchard\CronMonitorBundle\Traits\AutomaticCronMonitor;

class CronMonitorEventSubscriber implements EventSubscriberInterface
{

    /**
     * @var array
     */
    const EXCLUDED_ARGUMENTS = [
        'command',
    ];

    /**
     * @var array
     */
    const EXCLUDED_OPTIONS = [
        'ansi',
        'env',
        'help',
        'no-ansi',
        'no-debug',
        'no-interaction',
        'no-optional-warmers',
        'no-warmup',
        'quiet',
        'verbose',
        'version',
    ];

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND   => 'onConsoleBeforeStart',
            ConsoleEvents::ERROR     => 'onConsoleError',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
        ];
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleBeforeStart(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if ($this->hasAutomaticTrait($command) === false) {
            return;
        }
        /** @var AutomaticCronMonitor $command */
        $command->start($this->computeTokens($event->getInput()));
    }

    /**
     * @param ConsoleErrorEvent $event
     */
    public function onConsoleError(ConsoleErrorEvent $event)
    {
        $command = $event->getCommand();
        if ($this->hasAutomaticTrait($command) === false) {
            return;
        }
        $exception = $event->getError();
        $extraInformation = $this->computeExtraInformation($event->getOutput());
        /** @var AutomaticCronMonitor $command */
        $command->failure(
            array_merge(
                [
                    'trace'   => $exception->getTraceAsString(),
                    'message' => $exception->getMessage(),
                ],
                $extraInformation
            )
        );
    }

    /**
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        if ($this->hasAutomaticTrait($command) === false) {
            return;
        }
        $extraInformation = $this->computeExtraInformation($event->getOutput());
        switch ($event->getExitCode()) {
            case 0:
                $status = CronReporter::STATUS_SUCCESS;
                break;
            case 1:
                $status = CronReporter::STATUS_FAILED;
                break;
            case -1:
                $status = CronReporter::STATUS_LOCKED;
                break;
            case 65: # data format error
                $status = CronReporter::STATUS_CRITICAL;
                break;
            default:
                $status = CronReporter::STATUS_FAILED;
                break;
        }
        /** @var AutomaticCronMonitor $command */
        $command->end($status, $extraInformation);
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    private function computeTokens(InputInterface $input): array
    {
        $tokens = ['arguments' => [], 'options' => []];
        foreach ($input->getArguments() as $name => $value) {
            if (!in_array($name, self::EXCLUDED_ARGUMENTS) && !is_null($value) && !empty($value)) {
                $tokens['arguments'][$name] = $value;
            }
        }
        foreach ($input->getOptions() as $name => $value) {
            if (!in_array($name, self::EXCLUDED_OPTIONS) && !is_null($value) && !empty($value)) {
                $tokens['options'][$name] = $value;
            }
        }

        return ['tokens' => $tokens];
    }

    /**
     * @param OutputInterface $output
     *
     * @return array
     */
    private function computeExtraInformation(OutputInterface $output): array
    {
        $extraInformation = [];
        if ($output instanceof CronMonitorOutput) {
            $extraInformation['standard_output'] = $output->getBuffer();
            $extraInformation['output'] = $output->getBuffer();
        }
        $errorOutput = $output->getErrorOutput();
        if ($errorOutput instanceof CronMonitorStreamOutput) {
            $extraInformation['error_output'] = $errorOutput->getBuffer();
        }
        if ($output instanceof SharedBufferCronMonitorOutput) {
            $extraInformation['output'] = $output->getSharedBuffer();
        }

        return $extraInformation;
    }

    /**
     * @param Command|null $command
     *
     * @return bool
     */
    private function hasAutomaticTrait(Command $command = null): bool
    {
        if (is_null($command)) {
            return false;
        }
        $reflection = new \ReflectionClass($command);
        $traits = $reflection->getTraits();
        if (!array_key_exists(AutomaticCronMonitor::class, $traits)) {
            return false;
        }
        $reflection = null;

        return true;
    }
}
