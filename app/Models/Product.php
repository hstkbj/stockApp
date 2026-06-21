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
        'quantite',
        'seuil_alerte',
        'fournisseur_id',
        'date_expiration',
        'image',
        'category_id',
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

    /**
     * Le fournisseur de ce produit.
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * La catégorie de ce produit.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * L'utilisateur ayant créé/géré ce produit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les mouvements de stock de ce produit.
     */
    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }

    /**
     * Les lignes de facture contenant ce produit.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Les lignes d'approvisionnement contenant ce produit.
     */
    public function aprovisionnementItems(): HasMany
    {
        return $this->hasMany(AprovisionnementItem::class);
    }

    // Un produit apparaît dans plusieurs lignes de vente
    public function venteItems()
    {
        return $this->hasMany(VenteItem::class);
    }

    // Helper : stock en alerte
    public function enAlerte(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
    }

    // Helper : stock épuisé
    public function enRupture(): bool
    {
        return $this->quantite === 0;
    }
}
