<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AdminSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);

        $data = [
            [
                'name' => 'admin',
                'password' => $hashedPassword,
                'level' => 0,
                'remarks' => '初期管理者',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $this->table('admins')->insert($data)->saveData();
    }
}
