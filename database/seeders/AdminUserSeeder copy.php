<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin in Users Table
        // updateOrCreate use kiya hai taaki agar command dobara run ho toh duplicate error na aaye
        $admin = User::updateOrCreate(
            ['email' => 'vicky@compuhost.co.in'], // YAHAN APNA ADMIN EMAIL DALENGE
            [
                'name' => 'Admin',
                'username' => 'Admin@123',
                'password' => Hash::make('Admin@123'), // YAHAN APNA PASSWORD DALENGE
                'user_role' => 'admin',
            ]
        );

        // 2. Create Profile entry in UserInfos Table
        UserInfo::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'account_status' => 'active',
                'is_verified' => true,
            ]
        );
    }
}