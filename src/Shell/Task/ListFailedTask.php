<?php
namespace CakeQueue\Shell\Task;

use CakeQueue\TableRegistryTrait;
use Cake\Console\Shell;

class ListFailedTask extends Shell
{
    use TableRegistryTrait;

    /**
     * [main description]
     * @return void
     */
    public function main()
    {
        $table = $this->_failedJobsTable();
        $jobs = $table->find()
            ->select(['id', 'queue', 'payload', 'created_at'])
            ->all();

        $output = [
            ['ID', 'Queue', 'Job', 'Failed At']
        ];
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $output[] = [
                $job->id,
                $job->queue,
                $payload['job'],
                $job->created_at
            ];
        }
        $this->helper('Table')->output($output);
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
            'List all of the failed queue jobs'
        );

        return $parser;
    }
}
