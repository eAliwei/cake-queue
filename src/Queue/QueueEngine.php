<?php
namespace CakeQueue\Queue;

use Cake\Core\InstanceConfigTrait;
use InvalidArgumentException;

abstract class QueueEngine
{
    use InstanceConfigTrait;

    /**
     * The default queue configuration
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Initialize the queue engine
     *
     * @param array $config Associative array of parameters for the engine
     * @return bool True if the engine has been successfully initialized, false if not
     */
    public function init(array $config = [])
    {
        $this->setConfig($config);

        return true;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue Queue name
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: 'default';
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  mixed  $job job
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function _createPayload($job)
    {
        return json_encode($job);
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue Queue name
     * @return int
     */
    abstract public function size($queue = null);

    /**
     * Push a new job onto the queue.
     *
     * @param  mixed  $job Job
     * @param  string  $queue Queue name
     * @return mixed
     */
    abstract public function push($job, $queue = null);

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue Queue name
     * @return mixed
     */
    abstract public function pop($queue = null);
}
