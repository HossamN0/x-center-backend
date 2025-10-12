<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question',
        'image',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CourseExam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class,'question_id');
    }
}
