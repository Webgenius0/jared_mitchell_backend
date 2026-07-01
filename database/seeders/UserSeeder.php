<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $bossRole = Role::firstOrCreate(['name' => 'boss']);

        // Admin Users
        $admin1 = User::create([
            'email' => 'admin1@gmail.com',
            'phone' => '01710000001',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $admin1->assignRole($adminRole);

        $admin2 = User::create([
            'email' => 'admin2@gmail.com',
            'phone' => '01710000002',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $admin2->assignRole($adminRole);

        // Normal Users
        for ($i = 1; $i <= 10; $i++) {

            $user = User::create([
                'email' => "user{$i}@gmail.com",
                'phone' => "0181000000{$i}",
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole($userRole);
        }

        // Boss Users
        for ($i = 1; $i <= 10; $i++) {

            $boss = User::create([
                'email' => "boss{$i}@gmail.com",
                'phone' => "0191000000{$i}",
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $boss->assignRole($bossRole);
        }
    }
}
