<?php

namespace Tranchard\CronMonitorBundle\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class CronReporter
{

    const STATUS_STARTED  = 'started';
    const STATUS_FAILED   = 'failed';
    const STATUS_SUCCESS  = 'success';
    const STATUS_LOCKED   = 'locked';
    const STATUS_CRITICAL = 'critical';
    /**
     * @var array
     */
    public static $statuses = [
        self::STATUS_STARTED  => self::STATUS_STARTED,
        self::STATUS_SUCCESS  => self::STATUS_SUCCESS,
        self::STATUS_FAILED   => self::STATUS_FAILED,
        self::STATUS_LOCKED   => self::STATUS_LOCKED,
        self::STATUS_CRITICAL => self::STATUS_CRITICAL,
    ];

    /**
     * @var string|null
     * @Groups({"display"})
     */
    protected $id;

    /**
     * @var string
     * @Groups({"display", "create"})
     */
    protected $project;

    /**
     * @var string
     * @Groups({"display", "create"})
     */
    protected $job;

    /**
     * @var string|null
     * @Groups({"display", "create"})
     */
    protected $description;

    /**
     * @var \DateTime|null
     * @Groups({"display"})
     */
    protected $createdAt;

    /**
     * @var string
     * @Groups({"display", "create"})
     */
    protected $status;

    /**
     * @var int
     * @Groups({"display", "create"})
     */
    protected $duration;

    /**
     * @var array
     * @Groups({"display", "create"})
     */
    protected $extraPayload;

    /**
     * @var string
     * @Groups({"display", "create"})
     */
    protected $environment;

    /**
     * CronReporter constructor.
     *
     * @param string      $project
     * @param string      $job
     * @param string      $environment
     * @param string|null $description
     */
    public function __construct(string $project, string $job, string $environment, string $description = null)
    {
        $this->setProject($project);
        $this->setJob($job);
        $this->setDescription($description);
        $this->setStatus(self::STATUS_STARTED);
        $this->setCreatedAt(new \DateTime());
        $this->setDuration(0);
        $this->setExtraPayload([]);
        $this->setEnvironment($environment);
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|string $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @param string $project
     *
     * @return self
     */
    public function setProject(string $project): CronReporter
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return string
     */
    public function getJob(): string
    {
        return $this->job;
    }

    /**
     * @param string $job
     *
     * @return self
     */
    public function setJob(string $job): CronReporter
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description = null): CronReporter
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setStatus(string $status): CronReporter
    {
        if (!in_array($status, self::$statuses)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The status "%s" is not authorized. Authorized statuses: %s',
                    $status,
                    implode(',', self::$statuses)
                )
            );
        }
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return self
     */
    public function setDuration(int $duration): CronReporter
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraPayload(): array
    {
        return $this->extraPayload;
    }

    /**
     * @param array $extraPayload
     *
     * @return self
     */
    public function setExtraPayload(array $extraPayload): CronReporter
    {
        $this->extraPayload = $extraPayload;

        return $this;
    }

    /**
     * @param array $extraPayload
     *
     * @return CronReporter
     */
    public function addExtraPayload(array $extraPayload): CronReporter
    {
        $this->extraPayload = array_merge($this->extraPayload, $extraPayload);

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return self
     */
    public function setEnvironment(string $environment): CronReporter
    {
        $this->environment = $environment;

        return $this;
    }
}
