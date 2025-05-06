<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'payment_method',
        'payment_id',
        'amount',
        'currency',
        'starts_at',
        'expires_at',
        'cancelled_at',
        'auto_renew',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'metadata' => 'json',
        'amount' => 'float',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the subscription is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    /**
     * Check if the subscription is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    /**
     * Get the duration of the subscription in days
     *
     * @return int
     */
    public function getDurationDaysAttribute()
    {
        return $this->starts_at->diffInDays($this->expires_at);
    }

    /**
     * Get days remaining in the subscription
     *
     * @return int
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->expires_at);
    }

    /**
     * Get the subscription tier features
     *
     * @return array
     */
    public function getFeaturesAttribute()
    {
        $plans = [
            'free_trial' => [
                'max_connections' => 1,
                'servers' => ['standard'],
                'bandwidth' => 'Limited (2GB/day)',
                'vpn_protocols' => ['OpenVPN'],
                'speed' => 'Standard',
                'support' => 'Email',
                'duration' => '7 days',
            ],
            'basic' => [
                'max_connections' => 3,
                'servers' => ['standard'],
                'bandwidth' => 'Unlimited',
                'vpn_protocols' => ['OpenVPN'],
                'speed' => 'Standard',
                'support' => 'Email',
                'duration' => '30 days',
            ],
            'premium' => [
                'max_connections' => 5,
                'servers' => ['standard', 'premium'],
                'bandwidth' => 'Unlimited',
                'vpn_protocols' => ['OpenVPN', 'WireGuard'],
                'speed' => 'High-speed',
                'support' => 'Email, Chat',
                'duration' => '30 days',
            ],
            'business' => [
                'max_connections' => 10,
                'servers' => ['standard', 'premium', 'business'],
                'bandwidth' => 'Unlimited',
                'vpn_protocols' => ['OpenVPN', 'WireGuard', 'IKEv2'],
                'speed' => 'Maximum',
                'support' => 'Priority, 24/7',
                'duration' => '30 days',
            ],
        ];
        
        return $plans[$this->plan] ?? $plans['free_trial'];
    }
}
