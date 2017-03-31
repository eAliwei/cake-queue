<?php
namespace CakeQueue\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class QueueShell extends Shell
{
    public $tasks = [
        'CakeQueue.Worker',
        'CakeQueue.Table'
    ];

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        foreach ($this->_taskMap as $task => $option) {
            $taskParser = $this->{$task}->getOptionParser();
            $parser->addSubcommand(Inflector::underscore($task), [
                'help' => $taskParser->getDescription(),
                'parser' => $taskParser,
            ]);
        }

        return $parser;
    }
}
