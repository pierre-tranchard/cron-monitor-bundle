<?php

namespace Tranchard\CronMonitorBundle\Tests\Component\Console;

use Tranchard\CronMonitorBundle\Component\Console\CronMonitorOutput;
use PHPUnit\Framework\TestCase;

class CronMonitorOutputTest extends TestCase
{

    /**
     * Test buffer
     *
     * @group functional
     */
    public function testBuffer()
    {
        $output = new CronMonitorOutput();
        $output->writeln('hello folks');
        $this->assertEquals(PHP_EOL.'hello folks', $output->getBuffer());
        $output->clearBuffer();
        $this->assertEmpty($output->getBuffer());
        $output->write('goodbye folks', false);
        $this->assertEquals('goodbye folks', $output->getBuffer());
    }
}
