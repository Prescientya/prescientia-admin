<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $user = User::create([
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Create admin profile
        Admin::create([
            'user_id' => $user->id,
            'name' => 'Administrator',
            'nip' => 'ADM001',
            'phone_number' => '081234567890',
        ]);
    }
}
