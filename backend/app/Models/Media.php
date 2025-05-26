<?php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends BaseMedia
{

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($media) {
            $user = auth()->user();
            if ($user) {
                $media->uploaded_by = $user->id;
            }
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}