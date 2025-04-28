<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conference extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($conference) {
            if ($conference->is_latest) {
                static::where('id', '!=', $conference->id)
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);
            }
        });
    }

    protected $fillable = [
        'university_id',
        'created_by',
        'name',
        'title',
        'slug',
        'description',
        'location',
        'venue_details',
        'start_date',
        'end_date',
        'primary_color',
        'secondary_color',
        'is_latest',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_latest' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pageMenus(): HasMany
    {
        return $this->hasMany(PageMenu::class);
    }
    
    /**
     * The editors (users) that belong to the conference.
     */
    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('assigned_by', 'assigned_at')
                    ->withTimestamps();
    }
}
