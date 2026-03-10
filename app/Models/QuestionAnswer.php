<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'title',
        'image',
        'is_true',
    ];

    protected function casts(): array
    {
        return [
            'is_true' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_answer_id');
    }
}
