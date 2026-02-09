<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();

            // -----------------------------
            // Administrative names
            // -----------------------------
            $table->string('region');                 // ADM1_EN
            $table->string('province')->nullable();   // ADM2_EN (nullable for NCR edge cases)
            $table->string('city_municipality');      // ADM3_EN
            $table->string('barangay');               // ADM4_EN

            // -----------------------------
            // PSGC codes (authoritative IDs)
            // -----------------------------
            $table->string('psgc_region', 20)->nullable();      // ADM1_PCODE
            $table->string('psgc_province', 20)->nullable();    // ADM2_PCODE
            $table->string('psgc_city', 20)->nullable();        // ADM3_PCODE
            $table->string('psgc_barangay', 20)->unique();      // ADM4_PCODE

            // -----------------------------
            // Map coordinates (centroid)
            // -----------------------------
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->timestamps();

            // -----------------------------
            // Indexes for performance
            // -----------------------------
            $table->index(['region', 'province', 'city_municipality']);
            $table->index('barangay');
            $table->index('psgc_city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};

