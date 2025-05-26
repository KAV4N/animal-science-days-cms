<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageMenu extends Model
{

    protected $fillable = [
        'conference_id',
        'title',
        'slug',
        'order',
        'is_published',
        'created_by', 
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class, 'conference_id', 'id');
    }

    public function pageData(): HasMany
    {
        return $this->hasMany(PageData::class, 'menu_id', 'id');
    }
}