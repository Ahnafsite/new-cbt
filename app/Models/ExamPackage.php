<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPackage extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ExamPackageItem::class);
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_package_items');
    }

    public function studentExamCards(): HasMany
    {
        return $this->hasMany(StudentExamCard::class);
    }
}
