<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'emplacement_id',
        'quantite',
        'seuil_alerte',
    ];
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }
 
    /**
     * Vrai si la quantité est en dessous (ou égale) du seuil d'alerte.
     */
    public function getEnAlerteAttribute(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
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
