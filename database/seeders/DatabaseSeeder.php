<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Admin Seeder
        User::create(attributes: [
            'email' => 'ecotainment@gmail.com',
            'phone_number' => '08179123238',
            'password' => Hash::make('admin123'),
            'username' => 'Admin Ecotainment',
            'role' => 'admin',
        ]);
    }
}
