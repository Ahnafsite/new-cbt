<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSessionConfiguration extends Model
{
    protected $fillable = [
        'exam_session_id',
        'multiple_choice',
        'true_false',
        'short_essay',
        'essay',
    ];

    protected function casts(): array
    {
        return [
            'multiple_choice' => 'array',
            'true_false' => 'array',
            'short_essay' => 'array',
            'essay' => 'array',
        ];
    }

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }
}
