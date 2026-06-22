<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
     protected $fillable = [
        'code_barre',
        'nom',
        'prix_unitaire',
        'prix_achat',
        'fournisseur_id',
        'date_expiration',
        'image',
        'rayon_id',
        'user_id',
    ];
 
    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
            'prix_achat' => 'decimal:2',
            'date_expiration' => 'date',
        ];
    }
 
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }
 
    public function rayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class);
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    /**
     * Les lignes de stock de ce produit (une par emplacement).
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
 
    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }
 
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
 
    public function aprovisionnementItems(): HasMany
    {
        return $this->hasMany(AprovisionnementItem::class);
    }
 
    /**
     * Quantité totale en stock, tous emplacements confondus.
     */
    public function getQuantiteTotaleAttribute(): int
    {
        return $this->stocks->sum('quantite');
    }
 
    /**
     * Quantité en stock pour un emplacement donné.
     */
    public function stockPour(int $emplacementId): ?Stock
    {
        return $this->stocks->firstWhere('emplacement_id', $emplacementId);
    }

    
}
