<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use Models\User;

class UserSeeder extends AbstractSeed
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

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => password_hash('a', PASSWORD_DEFAULT), // 共通パスワード
                'status' => 1,
                'last_login_at' => $faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
                'birthday' => $faker->date(),
                'gender' => $faker->randomElement([1, 2]),
                'message' => $faker->realText(20),
                'profile' => $faker->realText(200),
                'notes' => null,
                'provider' => null,
                'provider_user_id' => null,
            ]);
        }
    }
}
