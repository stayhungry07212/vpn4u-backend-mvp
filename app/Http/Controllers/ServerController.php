<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Server;
use App\Services\ServerSelectionService;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{
    protected $serverSelectionService;

    public function __construct(ServerSelectionService $serverSelectionService)
    {
        $this->serverSelectionService = $serverSelectionService;
    }

    /**
     * Get all available servers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get user's current region from request (or default to auto-detect)
        $region = $request->get('region', 'auto');
        
        if ($region === 'auto') {
            // Simple IP-based region detection
            $userIp = $request->ip();
            // This would be more sophisticated in production
            $region = $this->detectRegionFromIp($userIp);
        }
        
        // Get servers, prioritizing the user's region
        $servers = Server::where('status', 'online')
            ->orderByRaw("CASE WHEN region = ? THEN 0 ELSE 1 END", [$region])
            ->orderBy('load', 'asc')
            ->get();
            
        return response()->json([
            'region' => $region,
            'servers' => $servers
        ]);
    }

    /**
     * Get recommended server based on user location and server load
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecommended(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get server metrics from cache
            $serverMetrics = Redis::hGetAll('server_metrics');
            
            // Get user's approximate location
            $userIp = $request->ip();
            $userRegion = $this->detectRegionFromIp($userIp);
            
            // Use service to find the best server
            $recommendedServer = $this->serverSelectionService->getOptimalServer($user, $userRegion, $serverMetrics);
            
            if (!$recommendedServer) {
                return response()->json(['error' => 'No suitable servers available'], 404);
            }
            
            return response()->json([
                'server' => $recommendedServer,
                'region' => $userRegion,
                'connection_data' => [
                    'protocol' => 'openvpn',
                    'port' => 1194
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Server selection error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to find recommended server'], 500);
        }
    }

    /**
     * Simple region detection from IP (demo implementation)
     * In production, this would use a proper IP geolocation service
     *
     * @param string $ip
     * @return string
     */
    private function detectRegionFromIp($ip)
    {
        // Simple demo implementation - would use MaxMind or similar in production
        // This just returns a random region for demo purposes
        $regions = ['us-east', 'us-west', 'eu-west', 'eu-central', 'ap-east', 'ap-south'];
        return $regions[array_rand($regions)];
    }
}
