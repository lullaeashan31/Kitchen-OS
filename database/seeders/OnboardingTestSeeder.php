<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\OnboardingToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OnboardingTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            OnboardingToken::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'token' => 'test-onboarding-token-123',
                    'expires_at' => now()->addDays(7),
                    'is_used' => false
                ]
            );
        }
    }
}
