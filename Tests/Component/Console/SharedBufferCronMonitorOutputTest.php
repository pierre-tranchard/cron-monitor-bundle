<?php

namespace Tranchard\CronMonitorBundle\Tests\Component\Console;

use Tranchard\CronMonitorBundle\Component\Console\SharedBufferCronMonitorOutput;
use PHPUnit\Framework\TestCase;

class SharedBufferCronMonitorOutputTest extends TestCase
{

    /**
     * Test buffer
     *
     * @group functional
     */
    public function testBuffer()
    {
        $output = new SharedBufferCronMonitorOutput();
        $output->writeln('hello folks');
        $this->assertEquals(PHP_EOL.'hello folks', $output->getBuffer());
        $output->clearBuffer();
        $this->assertEmpty($output->getBuffer());
        $output->write('goodbye folks', false);
        $this->assertEquals('goodbye folks', $output->getBuffer());
        $output->clearSharedBuffer();
    }

    /**
     * Test shared buffer
     *
     * @group functional
     */
    public function testSharedBuffer()
    {
        $output = new SharedBufferCronMonitorOutput();
        $output->writeln('hello folks');
        $output->getErrorOutput()->writeln('an error occurred');
        $this->assertEquals(PHP_EOL.'hello folks'.PHP_EOL.'an error occurred', $output->getSharedBuffer());
        $output->clearSharedBuffer();
        $this->assertEmpty($output->getSharedBuffer());
        $output->write('goodbye folks', false);
        $this->assertEquals('goodbye folks', $output->getSharedBuffer());
        $output->clearSharedBuffer();
        $output->getErrorOutput()->writeln('an exception was thrown');
        $this->assertEquals(PHP_EOL.'an exception was thrown', $output->getSharedBuffer());
        $output->clearSharedBuffer();
    }
}
