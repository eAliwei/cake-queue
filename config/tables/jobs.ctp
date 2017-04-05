<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Create{{tableClassName}} extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('{{table}}', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['autoIncrement' => true]);
        $table->addColumn('queue', 'string', ['limit' => 50]);
        $table->addColumn('payload', 'text', ['limit' => MysqlAdapter::TEXT_LONG]);
        $table->addColumn('attempts', 'integer', ['default' => 0, 'signed' => false]);
        $table->addColumn('reserved_at', 'integer', ['null' => true, 'signed' => false]);
        $table->addColumn('available_at', 'integer', ['signed' => false]);
        $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '']);
        $table->addIndex(['queue', 'reserved_at']);
        $table->create();
    }
}
