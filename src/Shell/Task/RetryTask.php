<?php
namespace CakeQueue\Shell\Task;

use CakeQueue\Queue\Queue;
use CakeQueue\TableRegistryTrait;
use Cake\Console\Shell;

class RetryTask extends Shell
{
    use TableRegistryTrait;

    /**
     * [main description]
     * @return void
     */
    public function main()
    {
        $where = !empty($this->args[0]) ? ['id' => $this->args[0]] : [];

        $table = $this->_failedJobsTable();
        $jobs = $table->find()
            ->select(['id', 'queue', 'payload'])
            ->where($where)
            ->all();

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $class = $payload['job'];
            if (class_exists($class)) {
                $retryJob = new $class($payload);
                Queue::push($retryJob, $job->queue);
                unset($retryJob);
                $table->delete($job);
                $this->info(sprintf('The failed job [%s] has been pushed back onto the queue!', $job->id));
            } else {
                $this->err('Not Found Job:' . $class);
            }
        }
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
            'Retry a failed queue job'
        );
        $parser->addArguments([
            'id' => ['help' => 'The ID of the failed job']
        ]);

        return $parser;
    }
}
