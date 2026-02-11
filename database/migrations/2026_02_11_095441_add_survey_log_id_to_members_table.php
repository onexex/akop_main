<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_log_id')
                  ->nullable()
                  ->after('address_id');

            // Optional: add FK if you have survey_logs table
            // $table->foreign('survey_log_id')
            //       ->references('id')
            //       ->on('survey_logs')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // If FK was added, drop it first
            // $table->dropForeign(['survey_log_id']);

            $table->dropColumn('survey_log_id');
        });
    }
};
