<?php
use CakeQueue\Queue\Queue;
use Cake\Core\Configure;

try {
    Configure::load('queue', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}
Queue::config(Configure::read('Queue'));
