<?php

namespace Tranchard\CronMonitorBundle\Transports\Drivers;

use Tranchard\CronMonitorBundle\Exception\TransportException;
use Tranchard\CronMonitorBundle\Model\CronReporter;

class Mailer extends AbstractTransport
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Mailer constructor.
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer = null)
    {
        $this->mailer = $mailer;
    }

    /**
     * @inheritdoc
     */
    public function send(CronReporter $cronReporter, callable $onSuccess = null, callable $onFailure = null): bool
    {
        if (is_null($this->mailer)) {
            throw TransportException::missingBundleException('SwiftMailer', self::getName());
        }
        $message = new \Swift_Message();
        $message->setSubject(
            sprintf(
                'Cron Monitor Fallback: %s %s %s',
                $cronReporter->getProject(),
                $cronReporter->getEnvironment(),
                $cronReporter->getJob()
            )
        );
        $message->setTo([$this->configuration['target']]);
        $payload = $cronReporter->getExtraPayload();
        $body = sprintf(
            "Status: %s\nOutput: %s\nTokens: %s\nTrace: %s\nMessage: %s\nData: %s",
            $cronReporter->getStatus(),
            $payload['output'] ?? '',
            json_encode($payload['tokens'] ?? []),
            $payload['trace'] ?? '',
            $payload['message'] ?? '',
            implode("\n", $payload['data'] ?? [])
        );
        $message->setBody($body, 'text/html');
        try {
            if ($this->mailer->send($message) === 0) {
                throw TransportException::failureException('Unable to send the message');
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
        return 'mailer';
    }
}
