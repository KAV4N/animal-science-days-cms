<?php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    public function mediaUserConference()
    {
        return $this->hasOne(MediaUserConference::class, 'media_id','id');
    }
}