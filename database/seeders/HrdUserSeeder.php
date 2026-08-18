<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class HrdUserSeeder extends Seeder
{
    /**
     * Akun HRD default untuk first-time setup.
     * Ganti password segera setelah login pertama di production.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('HRD_ADMIN_EMAIL', 'hrd@harris-hotel.local')],
            [
                'name' => env('HRD_ADMIN_NAME', 'HRD Admin'),
                'password' => env('HRD_ADMIN_PASSWORD', 'password'),
            ]
        );
    }
}
