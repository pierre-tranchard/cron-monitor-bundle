# Cron Monitor Bundle

This package is the client side for the cron monitor system.

Install the bundle in your client app as you're used to do and declare the configuration like the following.

```yaml
tranchard_cron_monitor:
    enabled: true
    project:
        name: Cron Monitor Demo App
    api:
        host: http://dev.cron-monitor.localhost
        path: api
        version: v1
        secret: ~
        timeout: 2.0
    fallback:
        transport: filesystem
        target: '%kernel.logs_dir%/cron-monitor'
```

## Fallback transport
1. Embedded transport
* The bundle provides two drivers, the filesystem and the mailer.
* The target node in the configuration is designed to be generic (an email, a folder, a slack channel...)

2. Make your own
* The fallback transport is designed to keep a trace of the exchange between the client and the API server.
* Basically you can implement your very own transport driver.
* All you need to do is to implement the `TransportInterface`, extends the `AbstractTransport` class and tag your system using the tag `tranchard.cron_monitor.transport`

## Console Component
* To get the output (stdout / stdrr) sent to the API, please update your console binary in your symfony app like the following
```php
$application->run($input, new SharedBufferCronMonitorOutput());
```
* Do not forget to import the class
```php
use Tranchard\CronMonitorBundle\Component\Console\SharedBufferCronMonitorOutput;
```
