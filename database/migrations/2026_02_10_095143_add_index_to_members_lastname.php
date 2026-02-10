<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            
            $table->index(['lastname', 'firstname'], 'idx_member_full_name');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
             
            $table->dropIndex('idx_member_full_name');
        });
    }
};