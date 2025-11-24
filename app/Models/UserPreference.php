<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_frequency',
        'journaling_frequency',
        'bible_study_time',
        'prayer_time',
        'meditation_time',
        'learning_cues',
        'journaling_cues',
        'bible_study_reminder',
        'prayer_reminder',
        'meditation_reminder',
    ];

    protected $casts = [
        'learning_cues' => 'array',
        'journaling_cues' => 'array',
        'bible_study_reminder' => 'boolean',
        'prayer_reminder' => 'boolean',
        'meditation_reminder' => 'boolean',
        'bible_study_time' => 'datetime:H:i',
        'prayer_time' => 'datetime:H:i',
        'meditation_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the available frequency options.
     */
    public static function getFrequencyOptions(): array
    {
        return ['daily', 'weekly'];
    }

    /**
     * Get the available cue options.
     */
    public static function getCueOptions(): array
    {
        return ['morning', 'afternoon', 'evening', 'after_dinner', 'before_bed'];
    }
}
