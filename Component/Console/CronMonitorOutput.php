<?php

namespace Tranchard\CronMonitorBundle\Component\Console;

use Symfony\Component\Console\Output\ConsoleOutput;

class CronMonitorOutput extends ConsoleOutput
{

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * CronMonitorOutput constructor.
     *
     * @inheritdoc
     */
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
        $this->setErrorOutput(
            new CronMonitorStreamOutput(
                $this->openErrorStream(), $verbosity, $decorated,
                $this->getFormatter()
            )
        );
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
    public function clearBuffer(): CronMonitorOutput
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
    }

    /**
     * @inheritdoc
     */
    protected function openErrorStream()
    {
        return fopen($this->hasStderrSupport() ? 'php://stderr' : 'php://output', 'w');
    }
}
