<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Logger extends AbstractMigration {

    /**
     */
    public function change(): void {
        // create the table
        $table = $this->table('log');
        $table
                ->addColumn('severity', 'string', ['comment' => 'message type'])
                ->addColumn('venue', 'string', ['comment' => 'message producer'])
                ->addColumn('message', 'text', ['comment' => 'main text'])
                ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->create();

                if ($this->adapter->getAdapterType() != 'sqlite') {
                    $table
                        ->changeColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
                        ->save();
                }
        

    }



}
