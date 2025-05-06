<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'region',
        'country_code',
        'city',
        'provider',
        'tier',
        'protocol',
        'port',
        'load',
        'capacity',
        'status',
        'public_ip',
        'last_checked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'load' => 'float',
        'capacity' => 'integer',
        'port' => 'integer',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Get the server's connections.
     */
    public function connections()
    {
        return $this->hasMany(Connection::class);
    }

    /**
     * Get the load percentage as integer (0-100)
     *
     * @return int
     */
    public function getLoadPercentAttribute()
    {
        return (int) $this->load;
    }

    /**
     * Get active connections count
     *
     * @return int
     */
    public function getActiveConnectionsAttribute()
    {
        return $this->connections()->where('status', 'active')->count();
    }

    /**
     * Get server usage percentage
     *
     * @return int
     */
    public function getUsagePercentAttribute()
    {
        $activeConnections = $this->getActiveConnectionsAttribute();
        return $this->capacity > 0 ? (int) (($activeConnections / $this->capacity) * 100) : 0;
    }

    /**
     * Determine if the server is at capacity
     *
     * @return bool
     */
    public function getIsAtCapacityAttribute()
    {
        return $this->getUsagePercentAttribute() >= 90;
    }

    /**
     * Get flag emoji for country code
     *
     * @return string
     */
    public function getFlagEmojiAttribute()
    {
        $countryCode = $this->country_code;
        
        // Convert country code to flag emoji
        $flag = "";
        $chars = str_split(strtoupper($countryCode));
        foreach ($chars as $char) {
            $flag .= mb_chr(ord($char) + 127397, 'UTF-8');
        }
        
        return $flag;
    }
}
