<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
    ];

    /**
     * Les produits fournis par ce fournisseur.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Les approvisionnements liés à ce fournisseur.
     */
    public function aprovisionnements(): HasMany
    {
        return $this->hasMany(Aprovisionnement::class);
    }
}
