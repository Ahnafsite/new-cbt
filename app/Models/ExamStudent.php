<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamStudent extends Model
{
    protected $fillable = [
        'exam_session_id',
        'student_id',
        'note',
        'session_data',
    ];

    protected function casts(): array
    {
        return [
            'session_data' => 'array',
        ];
    }

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(ExamReport::class);
    }
}
