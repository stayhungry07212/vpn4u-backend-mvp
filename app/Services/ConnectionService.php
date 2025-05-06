<?php

namespace App\Services;

use App\Models\User;
use App\Models\Connection;

class ConnectionService
{
    /**
     * Check if a user can create a new connection
     *
     * @param User $user
     * @return array
     */
    public function canUserConnect(User $user)
    {
        // Check if user has an active subscription
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$subscription) {
            return [
                'allowed' => false,
                'message' => 'No active subscription. Please subscribe to use VPN services.'
            ];
        }
        
        // Check how many active connections the user has
        $activeConnections = Connection::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // Get connection limit based on subscription plan
        $connectionLimit = $this->getUserConnectionLimit($user);
        
        if ($activeConnections >= $connectionLimit) {
            return [
                'allowed' => false,
                'message' => "Connection limit reached. Your plan allows {$connectionLimit} simultaneous connections."
            ];
        }
        
        return [
            'allowed' => true,
            'message' => 'Connection allowed',
            'active_connections' => $activeConnections,
            'limit' => $connectionLimit
        ];
    }
    
    /**
     * Get user's connection limit based on subscription
     *
     * @param User $user
     * @return int
     */
    public function getUserConnectionLimit(User $user)
    {
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$subscription) {
            return 0;
        }
        
        $limits = [
            'free_trial' => 1,
            'basic' => 3,
            'premium' => 5,
            'business' => 10,
        ];
        
        return $limits[$subscription->plan] ?? 1;
    }
    
    /**
     * Update connection statistics
     *
     * @param int $connectionId
     * @param array $stats
     * @return bool
     */
    public function updateConnectionStats($connectionId, $stats)
    {
        try {
            $connection = Connection::findOrFail($connectionId);
            
            $connection->update([
                'bytes_sent' => $stats['bytes_sent'] ?? $connection->bytes_sent,
                'bytes_received' => $stats['bytes_received'] ?? $connection->bytes_received,
                'last_active' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update connection stats: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark inactive connections as disconnected
     *
     * @param int $inactiveMinutes Connections inactive for this many minutes will be marked as disconnected
     * @return int Number of connections updated
     */
    public function cleanupInactiveConnections($inactiveMinutes = 15)
    {
        $cutoffTime = now()->subMinutes($inactiveMinutes);
        
        $inactiveConnections = Connection::where('status', 'active')
            ->where('last_active', '<', $cutoffTime)
            ->get();
            
        $count = 0;
        
        foreach ($inactiveConnections as $connection) {
            $connection->update([
                'status' => 'disconnected',
                'disconnected_at' => now(),
            ]);
            
            $count++;
        }
        
        return $count;
    }
}
