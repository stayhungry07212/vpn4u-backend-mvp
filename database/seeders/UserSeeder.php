<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@vpn4u.io',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create regular user
        User::create([
            'name' => 'Test User',
            'email' => 'test@vpn4u.io',
            'password' => Hash::make('test123'),
            'role' => 'user',
            'status' => 'active',
        ]);
    }
}