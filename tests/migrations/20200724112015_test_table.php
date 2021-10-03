<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TestTable extends AbstractMigration {

    /**
     * Change Method.
     */
    public function change(): void {
        $this->table('test')
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('value', 'text')
                ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated', 'timestamp', ['null' => true])
                ->create();
    }

}
