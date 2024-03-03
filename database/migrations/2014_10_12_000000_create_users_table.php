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
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->nullable();
            $table->string('email');
            $table->string('username');
            $table->string('image')->default('images/avatar.png');
            $table->string('provider')->default('null');
            $table->string('provider_id')->nullable();
            $table->string('provider_token',1000)->nullable();
            $table->enum('code',[1,2,3]);
            $table->enum('role',['student','teacher'])->nullable();
            $table->enum('grade',[1,2,3,4,5,6,7,8,9,10,11,12])->nullable();
            $table->enum('class',['A','B','C','D','E'])->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('last_active_at')->nullable();
            $table->boolean('online')->default(0);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->unique(["email", "provider"]);
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
