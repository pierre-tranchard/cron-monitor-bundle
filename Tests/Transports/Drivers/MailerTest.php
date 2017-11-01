<?php

namespace Tranchard\CronMonitorBundle\Tests\Transports\Drivers;

use Tranchard\CronMonitorBundle\Model\CronReporter;
use Tranchard\CronMonitorBundle\Transports\Drivers\Mailer;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{

    /**
     * Test get name
     */
    public function testGetName()
    {
        $this->assertEquals('mailer', Mailer::getName());
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
        $mock = $this->getMailer();
        $mock->expects($this->any())
             ->method('send')
             ->withAnyParameters()
             ->willReturn(1);
        $mailer = new Mailer($mock);
        $mailer->setConfiguration(['transport' => Mailer::getName(), 'target' => 'phpunit@allocine.fr']);
        $this->assertTrue($mailer->send($cronReporter, $onSuccess, $onFailure));
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
        $mock = $this->getMailer();
        $mock->expects($this->any())
             ->method('send')
             ->withAnyParameters()
             ->willReturn(0);
        $mailer = new Mailer($mock);
        $mailer->setConfiguration(['transport' => Mailer::getName(), 'target' => 'phpunit@allocine.fr']);
        $this->assertFalse($mailer->send($cronReporter, $onSuccess, $onFailure));
        $this->assertFileNotExists('/tmp/cron-reporter/success.txt');
        $this->assertFileExists('/tmp/cron-reporter/failure.txt');
        @unlink('/tmp/cron-reporter/success.txt');
        @unlink('/tmp/cron-reporter/failure.txt');
        $mock = $this->getMailer();
        $mock->expects($this->any())
             ->method('send')
             ->withAnyParameters()
             ->willReturn(1);
        $mailer = new Mailer($mock);
        $mailer->setConfiguration(['transport' => Mailer::getName(), 'target' => 'phpunit@allocine.fr']);
        $this->assertTrue($mailer->send($cronReporter, $onSuccess, $onFailure));
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMailer()
    {
        return $this->getMockBuilder(\Swift_Mailer::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
