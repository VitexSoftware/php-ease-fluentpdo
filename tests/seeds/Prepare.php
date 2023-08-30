<?php

use Phinx\Seed\AbstractSeed;

class Prepare extends AbstractSeed {

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void {
        $data = [
            [
                'name' => 'foo',
                'value' => 'a',
            ], [
                'name' => 'bar',
                'value' => 'b',
            ]
        ];

        $posts = $this->table('test');
        $posts->insert($data)
                ->saveData();
    }

}
