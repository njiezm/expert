<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockExamAnswer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rubric_check' => 'array',
            'diagram' => 'array',
            'points_awarded' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MockExamSession::class, 'mock_exam_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(MockExamQuestion::class, 'mock_exam_question_id');
    }
}