<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sub_servers', function (Blueprint $table) {
            $table->string('ipsec_user')->nullable()->after('wg_panel_password');
            $table->string('ipsec_password')->nullable()->after('ipsec_user');
            $table->string('ipsec_psk')->nullable()->after('ipsec_password');
            $table->string('ipsec_server')->nullable()->after('ipsec_psk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_servers', function (Blueprint $table) {
            $table->dropColumn('ipsec_user');
            $table->dropColumn('ipsec_password');
            $table->dropColumn('ipsec_psk');
            $table->dropColumn('ipsec_server');
        });
    }
};
