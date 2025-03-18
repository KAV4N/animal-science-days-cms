<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Conference extends Model implements HasMedia
{
    use InteractsWithMedia;


    protected $fillable = [
        'university_id',
        'title',
        'slug',
        'conference_date',
        'settings',
        'is_published',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_published' => 'boolean',
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
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('conference_files');
        $this->addMediaCollection('conference_images');
    }


    public function mediaUserConference()
    {
        return $this->hasMany(MediaUserConference::class, 'conference_id', 'id');
    }
}