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
        $this->call(EquipmentSetsTableSeeder::class);
        $this->call(SkillsTableSeeder::class);
    }
}
