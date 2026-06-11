<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('123456');

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'code' => 'ADMIN001',
                'name' => 'Admin',
                'phone' => '0123456789',
                'password' => $defaultPassword,
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'code' => 'STAFF001',
                'name' => 'Nhân viên',
                'phone' => '0987654321',
                'password' => $defaultPassword,
                'role' => 'staff',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['phone' => '0708091050'],
            [
                'code' => 'ADM001',
                'name' => 'Admin',
                'email' => 'admin@erp.local',
                'password' => Hash::make('101088'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
