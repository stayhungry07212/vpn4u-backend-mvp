<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Server;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $servers = [
            [
                'name' => 'US East 1',
                'hostname' => 'us-east-1.vpn4u.io',
                'ip_address' => '203.0.113.10',
                'region' => 'us-east',
                'country_code' => 'US',
                'city' => 'Virginia',
                'provider' => 'AWS',
                'tier' => 'standard',
                'protocol' => 'openvpn',
                'port' => 1194,
                'load' => 45.2,
                'capacity' => 1000,
                'status' => 'online',
                'public_ip' => '203.0.113.10',
            ],
            [
                'name' => 'US West 1',
                'hostname' => 'us-west-1.vpn4u.io',
                'ip_address' => '203.0.113.20',
                'region' => 'us-west',
                'country_code' => 'US',
                'city' => 'Oregon',
                'provider' => 'AWS',
                'tier' => 'standard',
                'protocol' => 'openvpn',
                'port' => 1194,
                'load' => 32.8,
                'capacity' => 1000,
                'status' => 'online',
                'public_ip' => '203.0.113.20',
            ],
            [
                'name' => 'EU West 1',
                'hostname' => 'eu-west-1.vpn4u.io',
                'ip_address' => '203.0.113.30',
                'region' => 'eu-west',
                'country_code' => 'IE',
                'city' => 'Dublin',
                'provider' => 'AWS',
                'tier' => 'premium',
                'protocol' => 'openvpn',
                'port' => 1194,
                'load' => 28.5,
                'capacity' => 1000,
                'status' => 'online',
                'public_ip' => '203.0.113.30',
            ],
            [
                'name' => 'EU Central 1',
                'hostname' => 'eu-central-1.vpn4u.io',
                'ip_address' => '203.0.113.40',
                'region' => 'eu-central',
                'country_code' => 'DE',
                'city' => 'Frankfurt',
                'provider' => 'AWS',
                'tier' => 'premium',
                'protocol' => 'openvpn',
                'port' => 1194,
                'load' => 52.3,
                'capacity' => 1000,
                'status' => 'online',
                'public_ip' => '203.0.113.40',
            ],
            [
                'name' => 'AP East 1',
                'hostname' => 'ap-east-1.vpn4u.io',
                'ip_address' => '203.0.113.50',
                'region' => 'ap-east',
                'country_code' => 'JP',
                'city' => 'Tokyo',
                'provider' => 'AWS',
                'tier' => 'business',
                'protocol' => 'openvpn',
                'port' => 1194,
                'load' => 18.7,
                'capacity' => 1000,
                'status' => 'online',
                'public_ip' => '203.0.113.50',
            ],
        ];

        foreach ($servers as $server) {
            Server::create($server);
        }
    }
}
