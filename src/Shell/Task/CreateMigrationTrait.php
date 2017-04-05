<?php
namespace CakeQueue\Shell\Task;

use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Utility\Inflector;
use Phinx\Util\Util;

trait CreateMigrationTrait
{
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
}
