<?php

namespace App\Services;

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ServerSelectionService
{
    /**
     * Get the optimal server for a user based on location and load
     *
     * @param User $user
     * @param string $userRegion
     * @param array $serverMetrics
     * @return Server|null
     */
    public function getOptimalServer(User $user, $userRegion, $serverMetrics = [])
    {
        // Get user's subscription plan to determine available server types
        $subscription = $this->getUserSubscriptionPlan($user);
        
        // Define server tiers available for different subscription plans
        $availableTiers = [
            'free_trial' => ['standard'],
            'basic' => ['standard'],
            'premium' => ['standard', 'premium'],
            'business' => ['standard', 'premium', 'business'],
        ];
        
        $allowedTiers = $availableTiers[$subscription] ?? ['standard'];
        
        // Find available servers in user's region first
        $regionalServers = Server::where('status', 'online')
            ->where('region', $userRegion)
            ->whereIn('tier', $allowedTiers)
            ->get();
            
        // If no servers in user's region, get servers from all regions
        if ($regionalServers->isEmpty()) {
            $regionalServers = Server::where('status', 'online')
                ->whereIn('tier', $allowedTiers)
                ->get();
        }
        
        if ($regionalServers->isEmpty()) {
            return null;
        }
        
        // Score servers based on load and latency
        $scoredServers = $this->scoreServers($regionalServers, $userRegion, $serverMetrics);
        
        // Return the server with the best score
        return $scoredServers->first();
    }
    
    /**
     * Score servers based on load and estimated latency
     *
     * @param \Illuminate\Support\Collection $servers
     * @param string $userRegion
     * @param array $serverMetrics
     * @return \Illuminate\Support\Collection
     */
    private function scoreServers($servers, $userRegion, $serverMetrics)
    {
        // Get latency estimates (would be from a real source in production)
        $latencyMap = $this->getLatencyEstimates($userRegion);
        
        return $servers->map(function ($server) use ($latencyMap, $serverMetrics) {
            // Get server's current load
            $load = $server->load;
            
            // If we have real-time metrics, use those instead
            if (isset($serverMetrics[$server->id])) {
                $metrics = json_decode($serverMetrics[$server->id], true);
                $load = $metrics['cpu_load'] ?? $load;
            }
            
            // Estimate latency based on region
            $latency = $latencyMap[$server->region] ?? 300;
            
            // Calculate score - lower is better
            // 70% weight on load, 30% on latency
            $score = ($load * 0.7) + ($latency * 0.3 / 10);
            
            $server->score = $score;
            return $server;
        })
        ->sortBy('score');
    }
    
    /**
     * Get user's subscription plan
     *
     * @param User $user
     * @return string
     */
    private function getUserSubscriptionPlan(User $user)
    {
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
            
        return $subscription ? $subscription->plan : 'free_trial';
    }
    
    /**
     * Get latency estimates for different regions
     * This would use real data in production
     *
     * @param string $userRegion
     * @return array
     */
    private function getLatencyEstimates($userRegion)
    {
        // This is a simplified mock. In production, this would use real latency measurements
        $baseLatencies = [
            'us-east' => [
                'us-east' => 30,
                'us-west' => 80,
                'eu-west' => 120,
                'eu-central' => 140,
                'ap-east' => 280,
                'ap-south' => 240,
            ],
            'us-west' => [
                'us-east' => 80,
                'us-west' => 30,
                'eu-west' => 150,
                'eu-central' => 170,
                'ap-east' => 220,
                'ap-south' => 260,
            ],
            'eu-west' => [
                'us-east' => 120,
                'us-west' => 150,
                'eu-west' => 30,
                'eu-central' => 50,
                'ap-east' => 240,
                'ap-south' => 200,
            ],
            'eu-central' => [
                'us-east' => 140,
                'us-west' => 170,
                'eu-west' => 50,
                'eu-central' => 30,
                'ap-east' => 220,
                'ap-south' => 180,
            ],
            'ap-east' => [
                'us-east' => 280,
                'us-west' => 220,
                'eu-west' => 240,
                'eu-central' => 220,
                'ap-east' => 30,
                'ap-south' => 120,
            ],
            'ap-south' => [
                'us-east' => 240,
                'us-west' => 260,
                'eu-west' => 200,
                'eu-central' => 180,
                'ap-east' => 120,
                'ap-south' => 30,
            ],
        ];
        
        return $baseLatencies[$userRegion] ?? $baseLatencies['us-east'];
    }
}
