<?php

namespace Tranchard\CronMonitorBundle\Tests\Transports\Drivers;

use Tranchard\CronMonitorBundle\Model\CronReporter;
use Tranchard\CronMonitorBundle\Transports\Drivers\Filesystem;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{

    /**
     * Test get name
     */
    public function testGetName()
    {
        $this->assertEquals('filesystem', Filesystem::getName());
    }

    /**
     * @dataProvider getDataWithoutCallbacks
     *
     * @param CronReporter  $cronReporter
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     */
    public function testSendWithoutCallbacks(
        CronReporter $cronReporter,
        callable $onSuccess = null,
        callable $onFailure = null
    ) {
        $filesystem = new Filesystem();
        $filesystem->setConfiguration(['transport' => Filesystem::getName(), 'target' => '/tmp/cron-reporter']);
        $this->assertTrue($filesystem->send($cronReporter, $onSuccess, $onFailure));
        @unlink(
            sprintf(
                '/tmp/cron-reporter/%s.%s.%s.txt',
                $cronReporter->getProject(),
                $cronReporter->getEnvironment(),
                $cronReporter->getJob()
            )
        );
    }

    /**
     * @dataProvider getDataWithCallbacks
     *
     * @param CronReporter $cronReporter
     * @param callable     $onSuccess
     * @param callable     $onFailure
     */
    public function testSendWithCallbacks(CronReporter $cronReporter, callable $onSuccess, callable $onFailure)
    {
        $filesystem = new Filesystem();
        $filesystem->setConfiguration(['transport' => Filesystem::getName(), 'target' => '/var']);
        $this->assertFalse($filesystem->send($cronReporter, $onSuccess, $onFailure));
        $this->assertFileNotExists('/tmp/cron-reporter/success.txt');
        $this->assertFileExists('/tmp/cron-reporter/failure.txt');
        @unlink('/tmp/cron-reporter/success.txt');
        @unlink('/tmp/cron-reporter/failure.txt');
        $filesystem = new Filesystem();
        $filesystem->setConfiguration(['transport' => Filesystem::getName(), 'target' => '/tmp/cron-reporter']);
        $this->assertTrue($filesystem->send($cronReporter, $onSuccess, $onFailure));
        $this->assertFileExists('/tmp/cron-reporter/success.txt');
        $this->assertFileNotExists('/tmp/cron-reporter/failure.txt');
        @unlink('/tmp/cron-reporter/success.txt');
        @unlink('/tmp/cron-reporter/failure.txt');
    }

    /**
     * @return array
     */
    public function getDataWithoutCallbacks()
    {
        return [
            [
                new CronReporter('phpunit', 'verification', 'test'),
                null,
                null,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getDataWithCallbacks()
    {
        return [
            [
                new CronReporter('phpunit', 'verification', 'test'),
                function (CronReporter $cronReporter) {
                    file_put_contents('/tmp/cron-reporter/success.txt', 'success');
                },
                function (CronReporter $cronReporter) {
                    file_put_contents('/tmp/cron-reporter/failure.txt', 'failure');
                },
            ],
        ];
    }
}
