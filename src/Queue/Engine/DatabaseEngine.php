<?php
namespace CakeQueue\Queue\Engine;

use CakeQueue\Jobs\DatabaseJob;
use CakeQueue\Queue\QueueEngine;
use Cake\Chronos\Chronos;
use Cake\Datasource\ConnectionManager;

class DatabaseEngine extends QueueEngine
{
    /**
     * Instance of ConnectionManager
     *
     * @var \Cake\Database\Connection
     */
    protected $_db;

    /**
     * The default queue configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'connection' => 'default',
        'table' => 'jobs',
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
        $this->_db = ConnectionManager::get($this->_config['connection']);

        return $this->_db->connect();
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue Queue name
     * @return int
     */
    public function size($queue = null)
    {
        return (int)$db->newQuery()
            ->select('COUNT(id) AS total')
            ->from($this->_config['table'])
            ->where(['queue' => $this->getQueue($queue)])
            ->execute()
            ->fetch('assoc')['total'];
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
     * @param  [type] $queue [description]
     * @param  [type] $job   [description]
     * @param  [type] $delay [description]
     * @return [type]        [description]
     */
    public function release($queue, $job, $delay)
    {
        return $this->_pushToDatabase(
            $queue,
            $job['payload'],
            $delay,
            $job['attempts']
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
        return $this->_db->insert(
            $this->_config['table'],
            [
                'queue' => $queue,
                'payload' => $payload,
                'attempts' => $attempts,
                'reserved_at' => null,
                'available_at' => Chronos::now()->addSeconds($delay)->getTimestamp(),
            ]
        )
        ->lastInsertId();
    }

    /**
     * [delete description]
     * @param  array $job [description]
     * @return mixed
     */
    public function delete($job)
    {
        return $this->_db->delete(
            $this->_config['table'],
            [
                'id' => $job['id']
            ]
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
        $this->_db->begin();
        if ($job = $this->_getNextAvailableJob($queue)) {
            return $this->_marshalJob($job, $queue);
        }
        $this->_db->commit();

        return null;
    }

    /**
     * [getNextAvailableJob description]
     * @param  string $queue [description]
     * @return array
     */
    protected function _getNextAvailableJob($queue)
    {
        return $this->_db->newQuery()
            ->select(['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at'])
            ->from($this->_config['table'])
            ->where([
                'queue' => $queue,
                'OR' => [
                    [
                        'reserved_at IS NULL',
                        'available_at <=' => Chronos::now()->getTimestamp()
                    ],
                    [
                        'reserved_at <=' => Chronos::now()->subSeconds($this->_config['retryAfter'])->getTimestamp()
                    ]
                ]
            ])
            ->order([
                'id' => 'ASC'
            ])
            ->limit(1)
            ->epilog('FOR UPDATE')
            ->execute()
            ->fetch('assoc');
    }

    /**
     * [_marshalJob description]
     * @param array $job [description]
     * @param string $queue [description]
     * @return array
     */
    protected function _marshalJob($job, $queue)
    {
        $job['reserved_at'] = Chronos::now()->getTimestamp();
        $job['attempts']++;
        $this->_db->update(
            $this->_config['table'],
            $job,
            ['id' => $job['id']]
        );
        $this->_db->commit();

        return new DatabaseJob($job, $this, $queue);
    }
}
