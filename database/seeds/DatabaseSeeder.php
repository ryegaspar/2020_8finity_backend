<?php

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Admin::factory()->create([
            'first_name' => 'john',
            'last_name'  => 'doe',
            'username'   => 'admin',
            'email'      => 'admin@admin.com',
            'password'   => bcrypt('password123')
        ]);

        $this->call(TransactionSeeder::class);
        // $this->call(UserSeeder::class);
    }
}
