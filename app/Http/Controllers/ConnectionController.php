<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Connection;
use App\Models\Server;
use App\Services\ConnectionService;
use App\Services\VpnService;
use Illuminate\Support\Facades\Log;

class ConnectionController extends Controller
{
    protected $connectionService;
    protected $vpnService;

    public function __construct(ConnectionService $connectionService, VpnService $vpnService)
    {
        $this->connectionService = $connectionService;
        $this->vpnService = $vpnService;
    }

    /**
     * Create a new VPN connection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connect(Request $request)
    {
        $request->validate([
            'server_id' => 'required|exists:servers,id',
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|in:windows,macos,linux,android,ios',
        ]);

        $user = auth()->user();
        $server = Server::findOrFail($request->server_id);
        
        try {
            // Check if user can create a new connection
            $canConnect = $this->connectionService->canUserConnect($user);
            
            if (!$canConnect['allowed']) {
                return response()->json([
                    'error' => 'Connection limit reached',
                    'message' => $canConnect['message']
                ], 403);
            }
            
            // Generate VPN credentials
            $vpnCredentials = $this->vpnService->generateClientCredentials(
                $user->id, 
                $request->device_name,
                $server->id
            );
            
            // Create connection record
            $connection = Connection::create([
                'user_id' => $user->id,
                'server_id' => $server->id,
                'device_name' => $request->device_name,
                'device_type' => $request->device_type,
                'public_ip' => $request->ip(),
                'virtual_ip' => $vpnCredentials['virtual_ip'],
                'protocol' => 'openvpn',
                'status' => 'connecting',
                'connected_at' => now(),
            ]);
            
            // Return connection details with VPN configuration
            return response()->json([
                'connection' => $connection,
                'server' => $server,
                'vpn_config' => $vpnCredentials['config'],
                'instructions' => $this->getInstructions($request->device_type)
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Connection creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to establish connection'], 500);
        }
    }

    /**
     * Disconnect from VPN
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnect($id)
    {
        try {
            $connection = Connection::findOrFail($id);
            
            // Check if the connection belongs to the authenticated user
            if ($connection->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Revoke VPN credentials
            $this->vpnService->revokeClientCredentials(
                $connection->user_id, 
                $connection->device_name
            );
            
            // Update connection status
            $connection->update([
                'status' => 'disconnected',
                'disconnected_at' => now(),
                'bytes_received' => $connection->bytes_received ?? 0,
                'bytes_sent' => $connection->bytes_sent ?? 0,
            ]);
            
            return response()->json([
                'message' => 'Successfully disconnected',
                'connection' => $connection
            ]);
            
        } catch (\Exception $e) {
            Log::error('Disconnect failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect'], 500);
        }
    }

    /**
     * Get user's connections
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserConnections()
    {
        $user = auth()->user();
        
        $connections = Connection::with('server')
            ->where('user_id', $user->id)
            ->orderBy('connected_at', 'desc')
            ->get();
        
        $activeCount = $connections->where('status', 'active')->count();
        $maxConnections = $this->connectionService->getUserConnectionLimit($user);
        
        return response()->json([
            'connections' => $connections,
            'stats' => [
                'active' => $activeCount,
                'limit' => $maxConnections,
                'available' => $maxConnections - $activeCount
            ]
        ]);
    }

    /**
     * Get device-specific instructions
     * 
     * @param string $deviceType
     * @return array
     */
    private function getInstructions($deviceType)
    {
        // In a real app, this would contain detailed, device-specific setup instructions
        $instructions = [
            'windows' => [
                'Download the OpenVPN client from the official website',
                'Import the configuration file',
                'Connect using your credentials'
            ],
            'macos' => [
                'Download Tunnelblick from the official website',
                'Import the configuration file',
                'Connect using your credentials'
            ],
            'linux' => [
                'Install OpenVPN client: sudo apt-get install openvpn',
                'Run: sudo openvpn --config your_config.ovpn'
            ],
            'android' => [
                'Download OpenVPN Connect from Google Play Store',
                'Import the configuration file',
                'Connect using your credentials'
            ],
            'ios' => [
                'Download OpenVPN Connect from App Store',
                'Import the configuration file via iTunes or email',
                'Connect using your credentials'
            ]
        ];
        
        return $instructions[$deviceType] ?? [];
    }
}
