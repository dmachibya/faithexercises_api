<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'category',
        'target_date',
        'is_completed',
        'progress',
    ];

    protected $casts = [
        'target_date' => 'date',
        'is_completed' => 'boolean',
        'progress' => 'integer',
    ];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the available goal types.
     */
    public static function getTypeOptions(): array
    {
        return ['yearly', 'fiveYear', 'quarterly', 'monthly'];
    }

    /**
     * Get the available goal categories.
     */
    public static function getCategoryOptions(): array
    {
        return ['spiritual', 'personal', 'professional', 'health', 'relationships', 'ministry'];
    }

    /**
     * Get the display name for the goal type.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'yearly' => 'Yearly Goal',
            'fiveYear' => '5-Year Goal',
            'quarterly' => 'Quarterly Goal',
            'monthly' => 'Monthly Goal',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the display name for the goal category.
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        return match ($this->category) {
            'spiritual' => 'Spiritual Growth',
            'personal' => 'Personal Development',
            'professional' => 'Professional',
            'health' => 'Health & Wellness',
            'relationships' => 'Relationships',
            'ministry' => 'Ministry & Service',
            default => ucfirst($this->category),
        };
    }

    /**
     * Check if the goal is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_completed && $this->target_date < Carbon::today();
    }

    /**
     * Get the days remaining until the target date.
     */
    public function getDaysRemainingAttribute(): int
    {
        return Carbon::today()->diffInDays($this->target_date, false);
    }

    /**
     * Scope to filter goals by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter goals by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter completed goals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to filter active (not completed) goals.
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope to filter overdue goals.
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
                    ->where('target_date', '<', Carbon::today());
    }
}
