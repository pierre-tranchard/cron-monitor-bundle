<?php

namespace Tranchard\CronMonitorBundle\Transports\Drivers;

use Tranchard\CronMonitorBundle\Exception\TransportException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Tranchard\CronMonitorBundle\Model\CronReporter;

class Filesystem extends AbstractTransport
{
    /**
     * @inheritdoc
     */
    public function send(CronReporter $cronReporter, callable $onSuccess = null, callable $onFailure = null): bool
    {
        $target = $this->configuration['target'];
        $name = sprintf(
            '%s.%s.%s.txt',
            $cronReporter->getProject(),
            $cronReporter->getEnvironment(),
            $cronReporter->getJob()
        );
        $fullPath = $target.DIRECTORY_SEPARATOR.$name;
        try {
            $filesystem = new SymfonyFilesystem();
            $filesystem->mkdir($target);
            $filesystem->touch($fullPath);
            if (file_put_contents($fullPath, serialize($cronReporter)) === false) {
                throw TransportException::failureException('Unable to write file');
            }
            if (!is_null($onSuccess)) {
                call_user_func($onSuccess, $cronReporter);
            }

            return true;
        } catch (\Exception $exception) {
            if (!is_null($onFailure)) {
                call_user_func($onFailure, $cronReporter);
            }

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'filesystem';
    }
}
