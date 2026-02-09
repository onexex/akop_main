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
        // Palitan ang pangalan mula 'barangays' papuntang 'address_properties'
        Schema::rename('barangays', 'address_properties');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ibalik sa dating pangalan kung sakaling i-rollback
        Schema::rename('address_properties', 'barangays');
    }
};