<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Conference extends Model
{

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
    
}