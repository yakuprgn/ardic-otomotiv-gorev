<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes'];

    public function assignments(): HasMany
    {
        return $this->hasMany(TrainingAssignment::class);
    }
}
