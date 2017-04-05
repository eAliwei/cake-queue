<?php
namespace CakeQueue\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;

class FailedTableTask extends Shell
{
    use CreateMigrationTrait;

    /**
     * [main description]
     * @return void
     */
    public function main()
    {
        $this->_createMigrationFile(Configure::readOrFail('Queue.failed.table'), 'failed_jobs.ctp');
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription(
            'Create a migration for the failed queue jobs database table'
        );

        return $parser;
    }
}
