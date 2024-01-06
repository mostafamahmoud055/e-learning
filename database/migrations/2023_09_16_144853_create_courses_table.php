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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->default('images/noImg.jpg');
            $table->enum('rate',[0,1,2,3,4,5])->default(0);
            $table->enum('grade',[1,2,3,4,5,6,7,8,9,10,11,12])->nullable();
            $table->timestamp('hours')->nullable();
            $table->json('target')->nullable();
            $table->boolean('active')->default(1);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(["name", "subject"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
