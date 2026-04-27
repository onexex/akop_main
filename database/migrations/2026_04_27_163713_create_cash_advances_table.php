<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_xx_xx_create_cash_advances_table.php

public function up(): void
{
    Schema::create('cash_advances', function (Blueprint $table) {
        $table->id();
        $table->string('ca_number')->unique();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('date');
        $table->string('district_office');
        $table->text('purpose');
        $table->text('beneficiaries');
        
        // Financial Details
        $table->decimal('amount_in_figure', 15, 2);
        $table->string('amount_in_words');

        /** * STATUS & APPROVAL FLOW 
         */
        
        // Pangunahing Status
        // Values: 'pending', 'first_approved', 'approved', 'rejected', 'cancelled'
        $table->string('status')->default('pending')->index();

        // Level 1: Supervisor / Dept Head
        $table->foreignId('first_approver_id')->nullable()->constrained('users');
        $table->timestamp('first_approved_at')->nullable();

        // Level 2: Finance / Manager
        $table->foreignId('second_approver_id')->nullable()->constrained('users');
        $table->timestamp('second_approved_at')->nullable();
        
        // Rejection / Archiving
        $table->foreignId('rejected_by')->nullable()->constrained('users');
        $table->text('rejection_reason')->nullable();
        
        // Metadata
        $table->timestamps();
        $table->softDeletes(); // Optional: para kung ma-delete, pwedeng i-restore
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};
