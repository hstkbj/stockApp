<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Les rayons appartenant à cette catégorie.
     */
    public function rayons(): HasMany
    {
        return $this->hasMany(Rayon::class);
    }
}
