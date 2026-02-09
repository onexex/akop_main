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
        Schema::create('survey_logs', function (Blueprint $table) {
            $table->id();
            $table->string('caption')->nullable();
            $table->text('remarks')->nullable();
            $table->text('address_tag')->nullable();
            $table->string('latitude', 50)->default('0.0');
            $table->string('longitude', 50)->default('0.0');
            $table->tinyInteger('sync_status')->default(0)->index(); // 0=Offline, 1=Synced
            $table->timestamps(); // Gagawa ito ng created_at at updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_logs');
    }
};
