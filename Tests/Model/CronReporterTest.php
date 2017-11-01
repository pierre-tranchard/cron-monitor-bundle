<?php

namespace Tranchard\CronMonitorBundle\Tests\Model;

use Tranchard\CronMonitorBundle\Model\CronReporter;
use PHPUnit\Framework\TestCase;

class CronReporterTest extends TestCase
{

    /**
     * Test count properties
     *
     * @group functional
     */
    public function testCountProperties()
    {
        $cronReporter = new CronReporter('fake_project', 'db update', 'prod');
        $reflection = new \ReflectionClass($cronReporter);
        $this->assertCount(10, $reflection->getProperties());
    }

    /**
     * Test constants
     *
     * @group functional
     */
    public function testConstants()
    {
        $this->assertEquals('success', CronReporter::STATUS_SUCCESS);
        $this->assertEquals('failed', CronReporter::STATUS_FAILED);
        $this->assertEquals('started', CronReporter::STATUS_STARTED);
        $this->assertEquals('locked', CronReporter::STATUS_LOCKED);
        $this->assertEquals('critical', CronReporter::STATUS_CRITICAL);
    }
}
