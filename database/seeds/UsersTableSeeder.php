<?php

use App\Models\User;
use App\Models\UserOAuth;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        factory(UserOAuth::class)->states('member')->create([
            'user_id' => factory(User::class)->states('member')->create()
        ]);

        factory(UserOAuth::class)->states('admin-member')->create([
            'user_id' => factory(User::class)->states('admin-member')->create()
        ]);

        factory(UserOAuth::class)->states('soulshriven')->create([
            'user_id' => factory(User::class)->states('soulshriven')->create()
        ]);
    }
}
