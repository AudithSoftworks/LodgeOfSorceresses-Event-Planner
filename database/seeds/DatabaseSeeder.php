<?php

use \Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(ContentTableSeeder::class);
//        $this->call(SetsTableSeeder::class);
//        $this->call(SkillsTableSeeder::class);
    }
}
