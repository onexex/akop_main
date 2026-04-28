<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->id();
            // Ang user na makakatanggap ng notification
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Para saan ang notification? (e.g., 'Cash Advance', 'User Access')
            $table->string('type')->nullable(); 
            
            $table->string('title');
            $table->text('message');
            
            // Dito natin ilalagay ang link papunta sa request (e.g., '/cash-advances?id=5')
            $table->string('url')->nullable();
            
            // Tracking kung nabasa na
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_notifications');
    }
};