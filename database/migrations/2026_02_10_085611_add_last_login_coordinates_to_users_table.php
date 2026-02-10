<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('last_login_lat', 10, 7)->nullable()->after('remember_token');
            $table->decimal('last_login_long', 10, 7)->nullable()->after('last_login_lat');

            // Optional: index for map/location queries
            $table->index(['last_login_lat', 'last_login_long']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_login_lat', 'last_login_long']);
            $table->dropColumn(['last_login_lat', 'last_login_long']);
        });
    }
};
