<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Interactive Super Admin creation — deliberately not a seeder with a
 * hardcoded password, per §6 "no shared accounts, every action attributable
 * to a named human". Run once at go-live, and again for each new named
 * Super Admin.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'traverse:create-admin';

    protected $description = 'Create a named Super Admin user for Traverse HR';

    public function handle(): int
    {
        $name = $this->ask('Full name');
        $email = $this->ask('Email');
        $password = $this->secret('Password (min 12 chars)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:12'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('super_admin');

        $this->info("Super Admin {$email} created. They must set up 2FA on first login.");

        return self::SUCCESS;
    }
}
