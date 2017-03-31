<?php
namespace CakeQueue\Shell\Task;

use CakeQueue\Jobs\SendEmailJob;
use CakeQueue\Queue\Queue;
use Cake\Chronos\Chronos;
use Cake\Console\Shell;
use Exception;
use RuntimeException;

class WorkerTask extends Shell
{
    /**
     * [main description]
     * @return void
     */
    public function main()
    {
        while (true) {
            try {
                if ($job = Queue::pop()) {
                    $class = $job->getJobName();
                    $this->out(sprintf(
                        'ID:%s Job:%s Attempts:%s',
                        $job->getJobId(),
                        $class,
                        $job->attempts()
                    ));
                    if (class_exists($class)) {
                        $run = new $class();
                        $run->data($job->getData())
                            ->handle();
                        $job->delete();
                        unset($run);
                    } else {
                        $this->err('Not Found Job:' . $class);
                    }
                }
            } catch (RuntimeException $e) {
                exit($e);
            } catch (Exception $e) {
                if ($job) {
                    // 重试次数
                    if ($job->attempts() < $job->maxTries()) {
                        $job->release(10);
                    } else {
                        // 超过重试次数删除job
                        if (method_exists($job, 'faild')) {
                            $job->faild($e);
                        }
                        $job->delete();
                    }
                }
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
            'WorkerTask Description'
        );

        return $parser;
    }
}
