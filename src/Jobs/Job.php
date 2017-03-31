<?php
namespace CakeQueue\Jobs;

use InvalidArgumentException;

abstract class Job
{
    protected $_queue;

    /**
     * [getQueue description]
     * @return string
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * [payload description]
     * @return array
     */
    public function payload()
    {
        return json_decode($this->getRawBody(), true);
    }

    /**
     * [getJobName description]
     * @return string
     */
    public function getJobName()
    {
        return $this->payload()['job'];
    }

    /**
     * [getData description]
     * @return mixed
     */
    public function getData()
    {
        return $this->payload()['data'];
    }

    /**
     * 最大尝试次数
     * @return int
     */
    public function maxTries()
    {
        return $this->payload()['maxTries'];
    }

    /**
     * [getRawBody description]
     * @return [type] [description]
     */
    abstract public function getRawBody();

    /**
     * [delete description]
     * @return mixed
     */
    abstract public function delete();

    /**
     * [release description]
     * @param  int $delay [description]
     * @return bool
     */
    abstract public function release($delay = 0);

    /**
     * [getJobId description]
     * @return mixed
     */
    abstract public function getJobId();

    /**
     * [attempts description]
     * @return int
     */
    abstract public function attempts();
}
