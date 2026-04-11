<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mật khẩu chung 123456
        $defaultPassword = Hash::make('123456');

        // Cập nhật hoặc tạo user Admin
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

        // Cập nhật hoặc tạo user Nhân viên
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
    }
}
