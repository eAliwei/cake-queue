<?php
namespace CakeQueue\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Utility\Inflector;
use Phinx\Util\Util;

class TableTask extends Shell
{
    /**
     * [initialize description]
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * [jobs description]
     * @return void
     */
    public function jobs()
    {
        $this->_createMigrationFile(Configure::readOrFail('Queue.connections.database.table'), 'jobs.ctp');
    }

    /**
     * [faildJobs description]
     * @return void
     */
    public function faildJobs()
    {
        $this->_createMigrationFile(Configure::readOrFail('Queue.failed.table'), 'failed_jobs.ctp');
    }

    /**
     * [_createMigrationFile description]
     * @param  string $table [description]
     * @param  string $tableTemplate   [description]
     * @return void
     */
    protected function _createMigrationFile($table, $tableTemplate)
    {
        $tableClassName = Inflector::camelize($table);
        $source = new File(Plugin::configPath('CakeQueue') . '/tables/' . $tableTemplate);
        $content = str_replace(
            ['{{table}}', '{{tableClassName}}'],
            [$table, $tableClassName],
            $source->read()
        );
        $path = CONFIG . 'Migrations/' . Util::getCurrentTimestamp() . '_Create' . $tableClassName . '.php';
        $dest = new File($path, true);
        if ($dest->write($content)) {
            $this->out(sprintf('Creating file %s', $path));
            $this->out(sprintf('<success>Wrote</success> `%s`', $path));
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
            'TableTask Description'
        );
        $parser->addSubcommand('jobs', [
            'help' => 'Create a migration for the queue jobs database table'
        ]);
        $parser->addSubcommand('faild_jobs', [
            'help' => 'Create a migration for the failed queue jobs database table'
        ]);

        return $parser;
    }
}
