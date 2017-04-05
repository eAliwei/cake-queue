<?php
namespace CakeQueue\Shell\Task;

use CakeQueue\Exception\FaildJobException;
use CakeQueue\Queue\Queue;
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
                        '<info>ID:%s Run:%s@handle</info> %s',
                        $job->getJobId(),
                        $class,
                        $job->attempts() > 1 ? 'Retry:' . ($job->attempts() - 1) : ''
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
                    if ($job->attempts() <= $job->maxTries()) {
                        $job->release();
                    } else {
                        // 超过重试次数删除job
                        $job->failed($e);
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
            'Start processing jobs on the queue as a daemon'
        );

        return $parser;
    }
}
