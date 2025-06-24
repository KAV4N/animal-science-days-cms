<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Conference extends Model implements HasMedia
{
    use InteractsWithMedia;

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
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        $this->addMediaCollection('general')
            ->acceptsMimeTypes([
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4', 'video/avi', 'video/mov'
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images', 'general');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->performOnCollections('images', 'general');
    }

    public function getImages()
    {
        return $this->getMedia('images');
    }

    public function getDocuments()
    {
        return $this->getMedia('documents');
    }

    public function getAllMedia()
    {
        return $this->getMedia('general');
    }
}

