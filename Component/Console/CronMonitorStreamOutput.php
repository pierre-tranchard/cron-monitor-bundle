<?php

namespace Tranchard\CronMonitorBundle\Component\Console;

use Symfony\Component\Console\Output\StreamOutput;

class CronMonitorStreamOutput extends StreamOutput
{

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var null|SharedBufferCronMonitorOutput
     */
    private $sharedBufferCronMonitorOutput;

    /**
     * CronMonitorStreamOutput constructor.
     *
     * {@inheritdoc}
     * @param SharedBufferCronMonitorOutput|null $sharedBufferCronMonitorOutput
     */
    public function __construct(
        $stream,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        $formatter = null,
        SharedBufferCronMonitorOutput $sharedBufferCronMonitorOutput = null
    ) {
        parent::__construct($stream, $verbosity, $decorated, $formatter);
        $this->sharedBufferCronMonitorOutput = $sharedBufferCronMonitorOutput;
    }

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @return self
     */
    public function clearBuffer(): CronMonitorStreamOutput
    {
        $this->buffer = '';

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function doWrite($message, $newline)
    {
        parent::doWrite($message, $newline);
        $this->buffer .= $newline ? PHP_EOL.$message : $message;
        if (!is_null($this->sharedBufferCronMonitorOutput)) {
            $this->sharedBufferCronMonitorOutput->addMessage($message, $newline);
        }
    }
}
