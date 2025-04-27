<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->belongsTo(University::class, 'university_id', 'id');
    }

    public function pageMenus(): HasMany
    {
        return $this->hasMany(PageMenu::class, 'conference_id', 'id');
    }

    public function conferenceEditors(): HasMany
    {
        return $this->hasMany(ConferenceEditor::class, 'conference_id', 'id');
    }
    
    public function editors()
    {
        return $this->hasManyThrough(
            User::class, 
            ConferenceEditor::class,
            'conference_id', // Foreign key on conference_editors table
            'id', // Foreign key on users table
            'id', // Local key on conferences table
            'user_id' // Local key on conference_editors table
        );
    }

    
    
}