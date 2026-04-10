<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Test User',
            'email' => 'testuser@email.com',  // Using 'admin' as email for testing
            'password' => Hash::make('password123'),
        ]);

                User::create([
            'name' => 'Test User',
            'email' => 'admin@email.com',  // Using 'admin' as email for testing
            'password' => Hash::make('password123'),
        ]);
    }
}
