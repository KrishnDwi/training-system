<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'is_mandatory',
        'standard_duration_minutes',
        'validity_months',
        'passing_score',
        'pretest_time_limit_minutes',
        'posttest_time_limit_minutes',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TrainingModuleQuestion::class)->orderBy('order_index');
    }

    public function employeeProgress(): HasMany
    {
        return $this->hasMany(EmployeeModuleProgress::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TrainingHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }
}
