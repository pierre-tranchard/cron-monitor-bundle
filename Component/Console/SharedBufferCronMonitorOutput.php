<?php

namespace Tranchard\CronMonitorBundle\Component\Console;

class SharedBufferCronMonitorOutput extends CronMonitorOutput
{

    /**
     * @var string
     */
    private static $sharedBuffer = '';

    /**
     * SharedBufferCronMonitorOutput constructor.
     *
     * @inheritdoc
     */
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
        $this->setErrorOutput(
            new CronMonitorStreamOutput(
                $this->openErrorStream(), $verbosity, $decorated,
                $this->getFormatter(), $this
            )
        );
    }

    /**
     * @return string
     */
    public function getSharedBuffer(): string
    {
        return self::$sharedBuffer;
    }

    /**
     * @param string $message
     * @param bool   $newline
     *
     * @return self
     */
    public function addMessage(string $message, bool $newline): SharedBufferCronMonitorOutput
    {
        self::$sharedBuffer .= $newline ? PHP_EOL.$message : $message;

        return $this;
    }

    /**
     * @return self
     */
    public function clearSharedBuffer(): SharedBufferCronMonitorOutput
    {
        self::$sharedBuffer = '';

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function doWrite($message, $newline)
    {
        parent::doWrite($message, $newline);
        self::$sharedBuffer .= $newline ? PHP_EOL.$message : $message;
    }
}
