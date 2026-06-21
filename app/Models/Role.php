<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Les utilisateurs ayant ce rôle.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Les permissions associées à ce rôle.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
