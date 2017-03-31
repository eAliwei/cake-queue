<?php
namespace CakeQueue\Queue;

use Cake\Chronos\Chronos;
use InvalidArgumentException;
use JsonSerializable;

abstract class Job implements JsonSerializable
{
    protected $_properties = [
        'data' => '',
        'delay' => 0,
        'maxTries' => 0
    ];

    /**
     * [__construct description]
     * @param array $properties   [description]
     */
    public function __construct(array $properties = [])
    {
        if (!empty($properties)) {
            foreach ($properties as $method => $value) {
                call_user_func_array([$this, $method], [$value]);
            }
        }
    }

    /**
     * [handle description]
     * @return void
     */
    abstract public function handle();

    /**
     * [delay description]
     * @param  $mixed $time [description]
     * @return $this
     */
    public function delay($time = 0)
    {
        if (is_int($time) && $time >= 0) {
            $this->_properties['delay'] = $time;
        } elseif ($time instanceof Chronos) {
            $this->_properties['delay'] = $time->diffInSeconds();
        } else {
            throw new InvalidArgumentException('Invalid delay time.');
        }

        return $this;
    }

    /**
     * [__toString description]
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * [jsonSerialize description]
     * @return array
     */
    public function jsonSerialize()
    {
        $this->_properties['job'] = get_class($this);

        return $this->_properties;
    }

    /**
     * [__call description]
     * @param  string $method Method name
     * @param  array $args    Args
     * @return this
     */
    public function __call($method, $args)
    {
        $count = count($args);
        if ($count === 0) {
            return isset($this->_properties[$method]) ? $this->_properties[$method] : null;
        }
        if ($count === 1) {
            $this->_properties[$method] = $args[0];
        }

        return $this;
    }

    /**
     * [__get description]
     * @param  string $key [description]
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->_properties[$key]) ? $this->_properties[$key] : false;
    }
}
