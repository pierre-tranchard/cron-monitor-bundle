<?php

namespace Tranchard\CronMonitorBundle\Exception;

class TransportException extends \Exception
{

    /**
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     *
     * @return TransportException
     */
    public static function failureException(string $message, int $code = 0, \Throwable $previous = null)
    {
        return new self($message, $code, $previous);
    }

    /**
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     *
     * @return TransportException
     */
    public static function notFoundException(string $message, int $code = 0, \Throwable $previous = null)
    {
        return new self($message, $code, $previous);
    }

    /**
     * @param string          $bundle
     * @param string          $transport
     * @param int             $code
     * @param \Throwable|null $previous
     *
     * @return TransportException
     */
    public static function missingBundleException(
        string $bundle,
        string $transport,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new self(
            sprintf('"%s" Bundle must be installed and enabled to use "%s" transport', $bundle, $transport),
            $code, $previous
        );
    }

    /**
     * @param string          $library
     * @param string          $transport
     * @param int             $code
     * @param \Throwable|null $previous
     *
     * @return TransportException
     */
    public static function missingLibraryException(
        string $library,
        string $transport,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new self(
            sprintf(
                '"%s" library must be added in your composer file to use "%" transport',
                $library,
                $transport
            ), $code, $previous
        );
    }
}
