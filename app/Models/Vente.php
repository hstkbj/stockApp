<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vente extends Model
{
     protected $fillable = [
        'reference',
        'montant_total',
        'montant_paye',
        'monnaie',
        'status',
        'date',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'monnaie' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * L'utilisateur ayant effectué cette vente.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Une vente a plusieurs lignes
    public function items()
    {
        return $this->hasMany(VenteItem::class);
    }

    // Les produits vendus (via la table pivot vente_items)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'vente_items')
                    ->withPivot('quantite', 'prix_unitaire', 'prix_total');
    }
}
