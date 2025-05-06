<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Server;
use App\Models\Connection;
use App\Models\Subscription;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Admin dashboard overview
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        // Count active users
        $activeUsers = User::where('status', 'active')->count();
        
        // Count active connections
        $activeConnections = Connection::where('status', 'active')->count();
        
        // Count online servers
        $onlineServers = Server::where('status', 'online')->count();
        
        // Calculate total bandwidth used today
        $todayBandwidth = Connection::where('connected_at', '>=', Carbon::today())
            ->sum(\DB::raw('bytes_sent + bytes_received'));
        
        // Format bandwidth in MB
        $bandwidthMB = round($todayBandwidth / (1024 * 1024), 2);
        
        // Count subscriptions by plan
        $subscriptionsByPlan = Subscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->select('plan', \DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->get()
            ->pluck('count', 'plan')
            ->toArray();
            
        // Get recent user registrations (last 7 days)
        $recentRegistrations = User::where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
            
        return response()->json([
            'active_users' => $activeUsers,
            'active_connections' => $activeConnections,
            'online_servers' => $onlineServers,
            'total_bandwidth_today_mb' => $bandwidthMB,
            'subscriptions_by_plan' => $subscriptionsByPlan,
            'recent_registrations' => $recentRegistrations,
        ]);
    }

    /**
     * List all users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request)
    {
        $query = User::query();
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $search = $request->search;
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Get paginated results
        $users = $query->with('subscriptions')
            ->withCount(['connections as active_connections' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 15);
            
        return response()->json($users);
    }

    /**
     * List all servers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function servers(Request $request)
    {
        $query = Server::query();
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }
        
        if ($request->has('tier')) {
            $query->where('tier', $request->tier);
        }
        
        // Get paginated results
        $servers = $query->withCount(['connections as active_connections' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy($request->sort_by ?? 'name', $request->sort_dir ?? 'asc')
            ->paginate($request->per_page ?? 15);
            
        return response()->json($servers);
    }

    /**
     * Create a new server
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createServer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'region' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'city' => 'nullable|string|max:255',
            'provider' => 'nullable|string|max:255',
            'tier' => 'required|in:standard,premium,business',
            'protocol' => 'required|in:openvpn,wireguard',
            'port' => 'required|integer|min:1|max:65535',
            'capacity' => 'required|integer|min:10',
            'public_ip' => 'required|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $server = Server::create($request->all());
        
        return response()->json([
            'message' => 'Server created successfully',
            'server' => $server
        ], 201);
    }

    /**
     * List all connections
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connections(Request $request)
    {
        $query = Connection::query();
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Get paginated results
        $connections = $query->with(['user', 'server'])
            ->orderBy($request->sort_by ?? 'connected_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 15);
            
        return response()->json($connections);
    }

    /**
     * Get system statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        // Get server stats
        $serverStats = Server::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
            
        // Get connection stats by device type
        $deviceStats = Connection::where('status', 'active')
            ->select('device_type', \DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();
            
        // Get connection stats by region
        $regionStats = Connection::where('status', 'active')
            ->join('servers', 'connections.server_id', '=', 'servers.id')
            ->select('servers.region', \DB::raw('count(*) as count'))
            ->groupBy('servers.region')
            ->get()
            ->pluck('count', 'region')
            ->toArray();
            
        // Get daily connection counts for the past 30 days
        $dailyConnections = Connection::where('connected_at', '>=', Carbon::now()->subDays(30))
            ->select(
                \DB::raw('DATE(connected_at) as date'),
                \DB::raw('count(*) as count')
            )
            ->groupBy(\DB::raw('DATE(connected_at)'))
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();
            
        // Fill in missing dates with zero counts
        $dateRange = [];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateRange[$date] = $dailyConnections[$date] ?? 0;
        }
        
        // Reverse to get chronological order
        $dateRange = array_reverse($dateRange);
            
        return response()->json([
            'server_stats' => $serverStats,
            'device_stats' => $deviceStats,
            'region_stats' => $regionStats,
            'daily_connections' => $dateRange,
        ]);
    }
}
