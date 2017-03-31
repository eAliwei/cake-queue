<?php
namespace CakeQueue\Jobs;

use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;

class BeanstalkdJob extends Job
{
    protected $_pheanstalk;
    protected $_job;

    /**
     * [__construct description]
     * @param PheanstalkJob $job        [description]
     * @param Pheanstalk    $pheanstalk [description]
     * @param string        $queue      [description]
     */
    public function __construct(PheanstalkJob $job, Pheanstalk $pheanstalk, $queue)
    {
        $this->_job = $job;
        $this->_pheanstalk = $pheanstalk;
        $this->_queue = $queue;
    }

    /**
     * 删除Job
     * @return mixed
     */
    public function delete()
    {
        return $this->_pheanstalk->useTube($this->_queue)->delete($this->_job);
    }

    /**
     * [release description]
     * @param  int $delay [description]
     * @return bool
     */
    public function release($delay = 0)
    {
        return $this->_pheanstalk->release($this->_job, Pheanstalk::DEFAULT_PRIORITY, $delay);
    }

    /**
     * Job 文本内容
     * @return string
     */
    public function getRawBody()
    {
        return $this->_job->getData();
    }

    /**
     * Job ID
     * @return int
     */
    public function getJobId()
    {
        return $this->_job->getId();
    }

    /**
     * 当前尝试次数
     *
     * @return int
     */
    public function attempts()
    {
        $stats = $this->_pheanstalk->statsJob($this->_job);

        return (int)$stats->reserves;
    }

    /**
     * 失败处理
     *
     * @return bool
     */
    public function faild()
    {
        return true;
    }
}
