<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@nupost.com'],
            [
                'name' => 'Admin Name',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'cerezoanielledaneb@gmail.com'],
            [
                'name' => 'Anielle Daneb Cerezo',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin_123!'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );
    }
}
