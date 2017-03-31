<?php
namespace CakeQueue\Queue;

use CakeQueue\Queue\QueueRegistry;
use Cake\Core\Configure;
use Cake\Core\ObjectRegistry;
use Cake\Core\StaticConfigTrait;
use InvalidArgumentException;
use RuntimeException;

class Queue
{
    use StaticConfigTrait;

    /**
     * Queue Registry used for creating and using queue adapters.
     *
     * @var \Cake\Core\ObjectRegistry
     */
    protected static $_registry;

    /**
     * Returns the Cache Registry instance used for creating and using cache adapters.
     * Also allows for injecting of a new registry instance.
     *
     * @param \Cake\Core\ObjectRegistry|null $registry Injectable registry object.
     * @return \Cake\Core\ObjectRegistry
     */
    public static function registry(ObjectRegistry $registry = null)
    {
        if ($registry) {
            static::$_registry = $registry;
        }

        if (!static::$_registry) {
            static::$_registry = new QueueRegistry();
        }

        return static::$_registry;
    }

    /**
     * [engine description]
     * @return \CakeQueue\Queue\QueueEngine
     */
    public static function engine()
    {
        $registry = static::registry();
        $name = static::$_config['default'];

        if (isset($registry->{$name})) {
            return $registry->{$name};
        }

        if (empty(static::$_config['connections'][$name]['className'])) {
            throw new InvalidArgumentException(
                sprintf('The "%s" queue configuration does not exist.', $name)
            );
        }

        $config = static::$_config['connections'][$name];
        $registry->load($name, $config);

        return $registry->{$name};
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue Queue name
     * @return int
     */
    public static function size($queue = null)
    {
        return static::engine()->size($queue);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  mixed  $job Job
     * @param  string  $queue Queue name
     * @return mixed
     */
    public static function push($job, $queue = null)
    {
        return static::engine()->push($job, $queue);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue Queue name
     * @return mixed
     */
    public static function pop($queue = null)
    {
        return static::engine()->pop($queue);
    }
}
