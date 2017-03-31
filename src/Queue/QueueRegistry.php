<?php
namespace CakeQueue\Queue;

use BadMethodCallException;
use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use RuntimeException;

class QueueRegistry extends ObjectRegistry
{
    /**
     * Resolve a queue engine classname.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class Partial classname to resolve.
     * @return string|false Either the correct classname or false.
     */
    protected function _resolveClassName($class)
    {
        if (is_object($class)) {
            return $class;
        }

        return App::className($class, 'Queue/Engine', 'Engine');
    }

    /**
     * Throws an exception when a queue engine is missing.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class The classname that is missing.
     * @param string $plugin The plugin the queue is missing in.
     * @return void
     * @throws \BadMethodCallException
     */
    protected function _throwMissingClassError($class, $plugin)
    {
        throw new BadMethodCallException(sprintf('Queue engine %s is not available.', $class));
    }

    /**
     * Create the queue engine instance.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string|\CakeQueue\Queue\array $class The classname or object to make.
     * @param string $alias The alias of the object.
     * @param array $config An array of settings to use for the queue engine.
     * @return \CakeQueue\Queue\QueueEngine The constructed QueueEngine class.
     * @throws \RuntimeException when an object doesn't implement the correct interface.
     */
    protected function _create($class, $alias, $config)
    {
        if (is_object($class)) {
            $instance = $class;
        }

        unset($config['className']);
        if (!isset($instance)) {
            $instance = new $class($config);
        }

        if (!($instance instanceof QueueEngine)) {
            throw new RuntimeException(
                'Queue engines must use CakeQueue\Queue\QueueEngine as a base class.'
            );
        }

        if (!$instance->init($config)) {
            throw new RuntimeException(
                sprintf('Queue engine %s is not properly configured.', get_class($instance))
            );
        }

        return $instance;
    }

    /**
     * Remove a single adapter from the registry.
     *
     * @param string $name The adapter name.
     * @return void
     */
    public function unload($name)
    {
        unset($this->_loaded[$name]);
    }
}
