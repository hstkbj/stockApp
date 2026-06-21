<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = [
        'role_id',
        'route_name',
        'create',
        'read',
        'update',
        'delete',
        'access_page',
    ];

    protected function casts(): array
    {
        return [
            'create' => 'boolean',
            'read' => 'boolean',
            'update' => 'boolean',
            'delete' => 'boolean',
            'access_page' => 'boolean',
        ];
    }

    /**
     * Le rôle auquel appartient cette permission.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
