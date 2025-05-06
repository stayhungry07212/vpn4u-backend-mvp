<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'server_id',
        'device_name',
        'device_type',
        'protocol',
        'public_ip',
        'virtual_ip',
        'status',
        'connected_at',
        'disconnected_at',
        'bytes_sent',
        'bytes_received',
        'last_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'bytes_sent' => 'integer',
        'bytes_received' => 'integer',
        'last_active' => 'datetime',
    ];

    /**
     * Get the user that owns the connection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the server that hosts the connection.
     */
    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the duration of the connection in seconds
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        $end = $this->disconnected_at ?? now();
        return $this->connected_at->diffInSeconds($end);
    }

    /**
     * Get the duration of the connection in human readable format
     *
     * @return string
     */
    public function getHumanDurationAttribute()
    {
        $end = $this->disconnected_at ?? now();
        return $this->connected_at->diffForHumans($end, true);
    }

    /**
     * Get the total data transferred in bytes
     *
     * @return int
     */
    public function getTotalDataAttribute()
    {
        return $this->bytes_sent + $this->bytes_received;
    }

    /**
     * Get the total data transferred in human readable format
     *
     * @return string
     */
    public function getHumanTotalDataAttribute()
    {
        $bytes = $this->getTotalDataAttribute();
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get the device icon based on device type
     *
     * @return string
     */
    public function getDeviceIconAttribute()
    {
        $icons = [
            'windows' => 'windows',
            'macos' => 'apple',
            'linux' => 'linux',
            'android' => 'android',
            'ios' => 'mobile',
        ];
        
        return $icons[$this->device_type] ?? 'desktop';
    }
}
