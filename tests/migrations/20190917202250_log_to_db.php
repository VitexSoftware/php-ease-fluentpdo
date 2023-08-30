<?php

use Phinx\Migration\AbstractMigration;

class LogToDb extends AbstractMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {

        $table = $this->table('log');
        $table->addColumn('severity', 'string', ['length' => 20, 'comment' => 'info|warning|error|..', 'default' => 'info'])
                ->addColumn('when', 'timestamp', ['comment' => 'log time', 'default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('venue', 'string', ['length' => 255, 'comment' => 'Message is Produced by', 'null' => true, 'default' => null])
                ->addColumn('message', 'text', ['comment' => 'Logged message itself', 'default' => ''])
                ->addColumn('application', 'text', ['comment' => 'App name', 'null' => 'true', 'default' => null])
                ->addColumn('user', 'integer', ['comment' => 'User ID', 'default' => 0])
                ->create();
        if ($this->adapter->getAdapterType() != 'sqlite') {
            $table
                ->changeColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
                ->save();
        }

    }

}
