# CakeQueue plugin for CakePHP

#### 安装

```
composer require ea-liwei/cake-queue

./bin/cake plugin load -b CakeQueue

# 使用database时执行
./bin/cake queue table jobs

# 失败job保存数据表
./bin/cake queue table faild_jobs

./bin/cake migrations migrate

```
#### 配置
```php
// config/queue.php
<?php
return [
    'Queue' => [
        'default' => env('QUEUE_ENGINE', 'database'),
        'failed' => [
            'enable' => true,
            'connection' => 'default',
            'table' => 'failed_jobs'
        ],
        'connections' => [
            'database' => [
                'className' => 'CakeQueue.Database',
                'connection' => 'default',
                'table' => 'jobs',
                'retryAfter' => 60
            ],
            'beanstalkd' => [
                'className' => 'CakeQueue.Beanstalkd',
                'host' => '127.0.0.1',
                'port' => 11300,
                'timeout' => 3,
                'persistent' => false
            ]
        ]
    ]
];
```

#### Sample
```php
// src/Jobs/SendMailJob.php
<?php
namespace App\Jobs;

use CakeQueue\Queue\Job;

class SendMailJob extends Job
{
    /**
     * [handle description]
     * @return void
     */
    public function handle()
    {
        $user = $this->data();
        // Mail::to($user['email'])
    }
}

// src/Controller/QueueController.php
<?php
namespace App\Controller;

use App\Jobs\SendMailJob;
use CakeQueue\Queue\Queue;

class QueueController extends AppController
{
    /**
     * [index description]
     * @return void
     */
    public function index()
    {
        $job = new SendMailJob();
        // 延迟3秒 失败最大重试3次
        $job->data(['email' => 'test@test.com'])->delay(3)->maxTries(3);
        pr(Queue::push($job));
    }
}
```

```bash
./bin/cake queue worker
```
