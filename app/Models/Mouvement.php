<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mouvement extends Model
{
    protected $fillable = [
        'product_id',
        'quantite',
        'type',
        'date',
        'motif',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Le produit concerné par ce mouvement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * L'utilisateur ayant effectué ce mouvement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
