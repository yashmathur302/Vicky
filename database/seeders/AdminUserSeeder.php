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
        $admin = User::updateOrCreate(
            ['username' => 'Admin@123'], // Yahan unique plain-text column use karna hai
            [
                'name' => 'Admin',
                'email' => 'vicky@compuhost.co.in', // Email ko update payload me daal dein
                'password' => Hash::make('Admin@123'),
                'user_role' => 'admin',
                'account_status' => 'active',
            ]
        );

         // 2. Create Profile entry in UserInfos Table
        UserInfo::updateOrCreate(
            ['user_id' => $admin->id],
            [
                // 'account_status' => 'active',
                'is_verified' => true,
            ]
        );
        
        // Create Admin in Users Table
        // $admin = User::updateOrCreate(
        //     ['email' => 'vicky@compuhost.co.in'],
        //     [
        //         'name' => 'Admin',
        //         'username' => 'Admin@123',
        //         'password' => Hash::make('Admin@123'),
        //         'user_role' => 'admin',
        //     ]
        // );

       
    }
}