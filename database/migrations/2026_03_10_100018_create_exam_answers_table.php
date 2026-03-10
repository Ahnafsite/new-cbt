<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->foreignId('exam_student_id')->constrained('exam_students')->cascadeOnDelete();
            $table->foreignId('exam_answer_id')->nullable()->constrained('question_answers')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->integer('elapsed_time')->nullable();
            $table->timestamps();

            $table->unique(['exam_question_id', 'exam_student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
