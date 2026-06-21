<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aprovisionnement extends Model
{
    protected $fillable = [
        'reference',
        'fournisseur_id',
        'montant_total',
        'date_approvisionnement',
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'date_approvisionnement' => 'datetime',
        ];
    }

    /**
     * Le fournisseur lié à cet approvisionnement.
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * L'utilisateur ayant créé cet approvisionnement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les lignes de cet approvisionnement.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AprovisionnementItem::class);
    }
}
