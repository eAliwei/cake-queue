<?php
namespace CakeQueue\Shell\Task;

use CakeQueue\TableRegistryTrait;
use Cake\Console\Shell;

class FlushFailedTask extends Shell
{
    use TableRegistryTrait;

    /**
     * [main description]
     * @return void
     */
    public function main()
    {
        $table = $this->_failedJobsTable();
        $table->deleteAll([1 => 1]);
        $this->info('All failed jobs deleted successfully!');
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
            'Flush all of the failed queue jobs'
        );

        return $parser;
    }
}
