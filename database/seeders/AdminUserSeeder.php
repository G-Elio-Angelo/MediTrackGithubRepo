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
                'email' => '23-36779@g.batstate-u.edu.ph',
                'password' => Hash::make('admin123'), // default password
                'phone_number' => '09666934242',
                'role' => 'admin', 
            ]
        );
    }
}
