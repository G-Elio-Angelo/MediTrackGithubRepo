<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@meditrack.com'], // checks if admin exists
            [
                'username' => 'Admin',
                'email' => 'admin@meditrack.com',
                'password' => Hash::make('admin123'), // default password
                'phone_number' => '09171234567',
                'role' => 'admin', 
            ]
        );
    }
}
