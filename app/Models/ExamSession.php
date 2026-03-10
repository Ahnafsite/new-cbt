<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamSession extends Model
{
    protected $fillable = [
        'title',
        'exam_id',
        'observer_id',
        'date',
        'time',
        'status',
        'room_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function configuration(): HasOne
    {
        return $this->hasOne(ExamSessionConfiguration::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(ExamSessionToken::class);
    }

    public function examStudents(): HasMany
    {
        return $this->hasMany(ExamStudent::class);
    }
}
