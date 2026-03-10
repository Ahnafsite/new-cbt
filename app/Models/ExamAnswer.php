<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_question_id',
        'exam_student_id',
        'exam_answer_id',
        'answer_text',
        'elapsed_time',
    ];

    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }

    public function examStudent(): BelongsTo
    {
        return $this->belongsTo(ExamStudent::class);
    }

    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(QuestionAnswer::class, 'exam_answer_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class);
    }
}
