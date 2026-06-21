<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenteItem extends Model
{
     public $timestamps = false;

    protected $fillable = [
        'vente_id', 'product_id', 'quantite',
        'prix_unitaire', 'prix_total'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'prix_total'    => 'decimal:2',
    ];

    // Appartient à une vente
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    // Appartient à un produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
