<?php
namespace CakeQueue\Queue\Engine;

use CakeQueue\Jobs\DatabaseJob;
use CakeQueue\Queue\QueueEngine;
use CakeQueue\TableRegistryTrait;
use Cake\Chronos\Chronos;

class DatabaseEngine extends QueueEngine
{
    use TableRegistryTrait;

    /**
     * Instance of Table
     *
     * @var \Cake\ORM\Table;
     */
    protected $_table;

    /**
     * The default queue configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'connectionName' => 'default',
        'table' => 'jobs',
        'model' => null,
        'retryAfter' => 60
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
     * Connects to a database server
     *
     * @return bool True if database server was connected
     */
    protected function _connect()
    {
        $this->_table = $this->_jobsTable($this->_config);

        return $this->_table->connection()->connect();
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue Queue name
     * @return int
     */
    public function size($queue = null)
    {
        return $this->_table->find()
            ->where(['queue' => $this->getQueue($queue)])
            ->count();
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
        return $this->_pushToDatabase(
            $this->getQueue($queue),
            $this->_createPayload($job),
            $job->delay,
            0
        );
    }

    /**
     * [release description]
     * @param  string $queue [description]
     * @param  \Cake\ORM\Entity $job   [description]
     * @param  int $delay [description]
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->_pushToDatabase(
            $queue,
            $job->payload,
            $delay,
            $job->attempts
        );
    }

    /**
     * [_pushToDatabase description]
     * @param  string $queue    [description]
     * @param  string  $payload  [description]
     * @param  int $delay    [description]
     * @param  int $attempts [description]
     * @return int
     */
    protected function _pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $job = $this->_table->newEntity([
            'queue' => $queue,
            'payload' => $payload,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => Chronos::now()->addSeconds($delay)->getTimestamp()
        ], ['validate' => false]);
        $this->_table->save($job);

        return $job->id;
    }

    /**
     * [delete description]
     * @param \Cake\ORM\Entity $job [description]
     * @return mixed
     */
    public function delete($job)
    {
        return $this->_table->delete($job);
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

        $this->_table->connection()->begin();
        if ($job = $this->_getNextJob($queue)) {
            return $this->_markJob($job, $queue);
        }
        $this->_table->connection()->commit();

        return null;
    }

    /**
     * [_getNextJob description]
     * @param  string $queue [description]
     * @return \Cake\ORM\Entity
     */
    protected function _getNextJob($queue)
    {
        return $this->_table->find()
            ->select(['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at'])
            ->where(function ($exp) {
                return $exp->lte('reserved_at', Chronos::now()->subSeconds($this->_config['retryAfter'])->getTimestamp());
            })
            ->orWhere(function ($exp) {
                return $exp->isNull('reserved_at')->lte('available_at', Chronos::now()->getTimestamp());
            })
            ->where(['queue' => $queue])
            ->orderAsc('id')
            ->epilog('FOR UPDATE')
            ->first();
    }

    /**
     * [_markJob description]
     * @param \Cake\ORM\Entity $job [description]
     * @param string $queue [description]
     * @return DatabaseJob
     */
    protected function _markJob($job, $queue)
    {
        $job = $this->_table->patchEntity($job, [
            'reserved_at' => Chronos::now()->getTimestamp(),
            'attempts' => $job->attempts + 1
        ], ['validate' => false]);

        $this->_table->save($job);
        $this->_table->connection()->commit();

        return new DatabaseJob($job, $this, $queue);
    }
}
