<?php

namespace App\Console\Commands;

use App\Helpers\FileHandle;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    protected $signature = 'user:create-super-admin {name} {email} {password}';

    protected $description = 'Create a new user and assign the super-admin role';

    public function handle(): int
    {
        $name     = $this->argument('name');
        $email    = $this->argument('email');
        $password = $this->argument('password');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");
            return self::FAILURE;
        }

        // Ensure super-admin role exists
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);

        // Create the user
        $user = User::create([
            'email'             => $email,
            'password'          => Hash::make($password),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        // Create profile
        $user->profile()->create([
            'name'     => $name,
            'username' => FileHandle::generateUsername($name),
            'slug'     => FileHandle::generateSlug($name),
        ]);

        // Assign role
        $user->assignRole($role);

        $this->info("Super admin '{$name}' ({$email}) created successfully!");

        return self::SUCCESS;
    }
}
