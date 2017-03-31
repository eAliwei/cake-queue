<?php
namespace CakeQueue\Queue\Engine;

use CakeQueue\Jobs\BeanstalkdJob;
use CakeQueue\Queue\QueueEngine;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class BeanstalkdEngine extends QueueEngine
{
    /**
     * Instance of Pheanstalk class
     *
     * @var \Pheanstalk\Pheanstalk
     */
    protected $_pheanstalk;

    /**
     * The default queue configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'host' => '127.0.0.1',
        'port' => 11300,
        'timeout' => 3,
        'persistent' => false
    ];

    /**
     * Initialize the Queue Engine
     *
     * Called automatically by the cache frontend
     *
     * @param array $config array of setting for the engine
     * @return bool True if the engine has been successfully initialized, false if not
     */
    public function init(array $config = [])
    {
        parent::init($config);

        return $this->_connect();
    }

    /**
     * Connects to a Beanstalkd server
     *
     * @return bool True if Beanstalkd server was connected
     */
    protected function _connect()
    {
        try {
            $this->_pheanstalk = new Pheanstalk(
                $this->_config['host'],
                $this->_config['port'],
                $this->_config['timeout'],
                $this->_config['persistent']
            );
        } catch (ConnectionException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue Queue name
     * @return int
     */
    public function size($queue = null)
    {
        return (int)$this->_pheanstalk->statsTube($this->getQueue($queue))->total_jobs;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  mixed  $job Job
     * @param  string  $queue Queue name
     * @return mixed
     */
    public function push($job, $queue = null)
    {
        return $this->_pheanstalk->useTube($this->getQueue($queue))->put(
            $this->_createPayload($job),
            $job->priority ?: Pheanstalk::DEFAULT_PRIORITY,
            $job->delay ?: Pheanstalk::DEFAULT_DELAY,
            $job->ttr ?: Pheanstalk::DEFAULT_TTR
        );
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue Queue name
     * @return  mixed
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        $job = $this->_pheanstalk->watchOnly($queue)->reserve(0);
        if ($job instanceof Job) {
            return new BeanstalkdJob($job, $this->_pheanstalk, $queue);
        }

        return null;
    }
}
