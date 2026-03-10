<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamReport extends Model
{
    protected $fillable = [
        'exam_student_id',
        'score',
        'note',
    ];

    public function examStudent(): BelongsTo
    {
        return $this->belongsTo(ExamStudent::class);
    }
}
