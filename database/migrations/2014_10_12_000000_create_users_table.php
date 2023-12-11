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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('lastName')->nullable();
            $table->string('imageId')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('verifycodeSendTime')->nullable();
            $table->timestamp('verifycodeExpiryTime')->nullable();
            $table->string('verifycodeForLogin')->nullable();
            $table->string('verifycodeForResetPassword')->nullable();
            $table->string('verifycodeForResetMobile')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
