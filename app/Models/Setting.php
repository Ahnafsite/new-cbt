<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'school_name',
        'address',
        'city',
        'phone',
        'email',
        'logo',
        'chairman',
        'nip',
    ];
}
