<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('suffix')->nullable();

            $table->unsignedBigInteger('address_id')->nullable();

            $table->unsignedBigInteger('taggedBy')->nullable();
            $table->boolean('isTagged')->default(false);

            $table->decimal('last_login_lat', 10, 7)->nullable();
            $table->decimal('last_login_long', 10, 7)->nullable();

            $table->timestamps();

            // Optional foreign keys (uncomment if applicable)
            // $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
            // $table->foreign('taggedBy')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
