<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_type'); // windows, macos, linux, android, ios
            $table->string('protocol')->default('openvpn'); // openvpn, wireguard
            $table->ipAddress('public_ip');
            $table->ipAddress('virtual_ip');
            $table->string('status'); // connecting, active, disconnected, error
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->bigInteger('bytes_sent')->default(0);
            $table->bigInteger('bytes_received')->default(0);
            $table->timestamp('last_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connections');
    }
}