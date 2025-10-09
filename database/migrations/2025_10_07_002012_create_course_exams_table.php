<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('course_chapters')->onDelete('cascade');
            $table->float('degree')->default(0);
            $table->enum('status', ['not_started', 'inprogress', 'completed'])->default('not_started');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_exams');
    }
};
