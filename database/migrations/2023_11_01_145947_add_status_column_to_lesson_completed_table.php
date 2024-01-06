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
        Schema::table('lesson_completed', function (Blueprint $table) {
            $table->enum('status',['completed','inprogress'])->nullable()->after('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_completed', function (Blueprint $table) {
            $table->dropColumn('lessonCompleted');
        });
    }
};
