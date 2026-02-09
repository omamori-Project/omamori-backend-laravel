<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@omamori.com',
                'password_hash' => Hash::make('password'),
                'name' => '관리자',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'jungmin@omamori.com',
                'password_hash' => Hash::make('password'),
                'name' => '정민',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'user1@example.com',
                'password_hash' => Hash::make('password'),
                'name' => '사용자1',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'user2@example.com',
                'password_hash' => Hash::make('password'),
                'name' => '사용자2',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        // User Identities 생성
        $userIds = DB::table('users')->pluck('id', 'email');
        
        $identities = [];
        foreach ($users as $user) {
            $identities[] = [
                'user_id' => $userIds[$user['email']],
                'provider' => 'local',
                'provider_user_id' => $user['email'],
                'email' => $user['email'],
                'password_hash' => $user['password_hash'],
                'profile' => json_encode([
                    'name' => $user['name'],
                ]),
                'linked_at' => now(),
                'last_used_at' => now(),
            ];
        }

        DB::table('user_identities')->insert($identities);
    }
}