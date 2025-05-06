<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create subscription for test user
        Subscription::create([
            'user_id' => 2, // Test user ID from UserSeeder
            'plan' => 'premium',
            'status' => 'active',
            'payment_method' => 'credit_card',
            'payment_id' => 'test_payment_123',
            'amount' => 9.99,
            'currency' => 'USD',
            'starts_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMonth(),
            'auto_renew' => true,
        ]);
    }
}
