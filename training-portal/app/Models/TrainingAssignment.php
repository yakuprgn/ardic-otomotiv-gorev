<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAssignment extends Model
{
    protected $fillable = [
        'training_id', 'employee_id', 'employee_name', 'employee_email',
        'employee_department', 'assigned_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
