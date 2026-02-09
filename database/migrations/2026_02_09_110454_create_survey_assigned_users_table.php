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
            Schema::create('survey_assigned_users', function (Blueprint $table) {
                $table->id();
                // Foreign key na naka-link sa survey_logs
                $table->foreignId('survey_log_id')
                    ->constrained('survey_logs')
                    ->onDelete('cascade'); 
                    
                $table->string('assigned_user', 100)->index(); // EmpUN
                $table->string('latitude', 50)->default('0.0');
                $table->string('longitude', 50)->default('0.0');
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_assigned_users');
    }
};
