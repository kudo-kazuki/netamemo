<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DummyUserSeeder extends AbstractSeed
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
        $faker = Faker\Factory::create('ja_JP');

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'name' => $faker->name,
                'gender' => $faker->numberBetween(0, 1),
                'age' => $faker->numberBetween(18, 60),
                'remarks' => $faker->realText(50),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('dummy_user')->insert($data)->saveData();
    }
}
