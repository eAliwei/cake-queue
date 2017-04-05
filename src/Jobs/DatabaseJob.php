<?php
namespace CakeQueue\Jobs;

use CakeQueue\Queue\Engine\DatabaseEngine;
use Cake\ORM\Entity;

class DatabaseJob extends Job
{
    protected $_engine;
    protected $_job;

    /**
     * [__construct description]
     * @param \Cake\ORM\Entity $job             [description]
     * @param DatabaseEngine $engine            [description]
     * @param string        $queue              [description]
     */
    public function __construct(Entity $job, DatabaseEngine $engine, $queue)
    {
        $this->_job = $job;
        $this->_engine = $engine;
        $this->_queue = $queue;
    }

    /**
     * 删除Job
     * @return mixed
     */
    public function delete()
    {
        return $this->_engine->delete($this->_job);
    }

    /**
     * [release description]
     * @param  int $delay [description]
     * @return bool
     */
    public function release($delay = 0)
    {
        $this->delete();

        return $this->_engine->release($this->_queue, $this->_job, $delay);
    }

    /**
     * Job 文本内容
     * @return string
     */
    public function getRawBody()
    {
        return $this->_job->payload;
    }

    /**
     * Job ID
     * @return int
     */
    public function getJobId()
    {
        return $this->_job->id;
    }

    /**
     * 当前尝试次数
     *
     * @return int
     */
    public function attempts()
    {
        return $this->_job->attempts;
    }
}
