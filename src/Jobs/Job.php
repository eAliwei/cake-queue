<?php
namespace CakeQueue\Jobs;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
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
     * [failed description]
     * @param  [type] $e [description]
     * @return void
     */
    public function failed($e)
    {
        if (Configure::read('Queue.failed.enable') === true) {
            $connection = Configure::readOrFail('Queue.failed.connection');
            $table = Configure::readOrFail('Queue.failed.table');
            $db = ConnectionManager::get($connection);
            $db->insert($table, [
                'queue' => $this->getQueue(),
                'payload' => $this->getRawBody(),
                'exception' => $e->__toString()
            ]);
        }
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
