<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'inaya-creation@gmail.com'], // unique check
            [
                'name' => 'Admin',
                'password' => Hash::make('Nurraju123'), // change this!
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('Super Admin');
    }
}
