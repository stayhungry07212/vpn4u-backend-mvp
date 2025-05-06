<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname');
            $table->string('ip_address');
            $table->string('region'); // us-east, eu-west, etc.
            $table->string('country_code', 2);
            $table->string('city')->nullable();
            $table->string('provider')->nullable(); // AWS, GCP, etc.
            $table->string('tier')->default('standard'); // standard, premium, business
            $table->string('protocol')->default('openvpn'); // openvpn, wireguard
            $table->integer('port')->default(1194);
            $table->float('load')->default(0); // 0-100%
            $table->integer('capacity')->default(1000); // Maximum number of connections
            $table->string('status')->default('online'); // online, offline, maintenance
            $table->ipAddress('public_ip');
            $table->timestamp('last_checked_at')->nullable();
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
        Schema::dropIfExists('servers');
    }
}
