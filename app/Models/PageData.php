<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageData extends Model
{
    protected $fillable = [
        'menu_id',
        'component_type',
        'order',
        'data',
        'is_published',
        'created_by', 
        'updated_by',
    ];

    protected $casts = [
        'data' => 'array',
        'is_published' => 'boolean',
    ];

    public function pageMenu(): BelongsTo
    {
        return $this->belongsTo(PageMenu::class, 'menu_id', 'id');
    }
}