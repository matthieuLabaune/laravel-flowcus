<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Primary development user
        User::query()->firstOrCreate(
            ['email' => 'matthieu.labaune@gmail.com'],
            [
                'name' => 'Matthieu Labaune',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
