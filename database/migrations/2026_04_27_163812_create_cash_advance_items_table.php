<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_cash_advance_items_table.php

        public function up(): void
        {
            Schema::create('cash_advance_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_advance_id')->constrained()->cascadeOnDelete();
                
                $table->string('details'); // Description
                $table->integer('qty');
                
                // Currency breakdown
                $table->decimal('amount_usd', 15, 2)->default(0);
                $table->decimal('amount_peso', 15, 2)->default(0);
                
                // Auto-computed sum (qty * peso)
                $table->decimal('total', 15, 2); 
                
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advance_items');
    }
};
