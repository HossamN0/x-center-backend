<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseExam extends Model
{
    protected $fillable = [
        'chapter_id',
        'end_time',
        'start_time',
        'status',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }
}
