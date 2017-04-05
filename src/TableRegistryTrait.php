<?php
namespace CakeQueue;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

trait TableRegistryTrait
{
    /**
     * [_registryTable description]
     * @param  string $alias  [description]
     * @param  array $config [description]
     * @return \Cake\ORM\Table
     */
    protected function _registryTable($alias, array $config)
    {
        return !empty($config['model'])
            ? TableRegistry::get($alias, ['className' => $config['model']])
            : TableRegistry::get($alias, [
                'table' => $config['table'],
                'connectionName' => $config['connectionName']
            ]);
    }

    /**
     * [_jobsTable description]
     * @param  array $config [description]
     * @return \Cake\ORM\Table
     */
    protected function _jobsTable(array $config)
    {
        return $this->_registryTable('Jobs', $config);
    }

    /**
     * [_failedJobsTable description]
     * @return \Cake\ORM\Table
     */
    protected function _failedJobsTable()
    {
        return $this->_registryTable('FailedJobs', Configure::readOrFail('Queue.failed'));
    }
}
