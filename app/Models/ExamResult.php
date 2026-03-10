<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_answer_id',
        'point',
        'note',
        'corrected_by',
    ];

    public function examAnswer(): BelongsTo
    {
        return $this->belongsTo(ExamAnswer::class);
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
