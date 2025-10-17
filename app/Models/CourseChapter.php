<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseChapter extends Model
{
    protected $fillable = [
        'course_id',
        'status',
        'title',
        'content',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function exam(): HasOne
    {
        return $this->hasOne(CourseExam::class,'chapter_id');
    }
}
