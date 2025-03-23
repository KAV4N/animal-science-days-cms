<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class University extends Model
{
    protected $fillable = [
        'full_name',
        'country',
        'city',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'university_id', 'id');
    }

    public function conferences(): HasMany
    {
        return $this->hasMany(Conference::class, 'university_id', 'id');
    }
}