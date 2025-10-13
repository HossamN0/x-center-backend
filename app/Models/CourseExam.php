<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseExam extends Model
{
    protected $fillable = [
        'chapter_id',
        'end_time',
        'start_time',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }

    public function studentDegree(): HasOne
    {
        return $this->hasOne(StudentDegree::class, 'exam_id');
    }
}
