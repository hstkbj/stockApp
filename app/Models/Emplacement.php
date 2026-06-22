<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emplacement extends Model
{
     // Valeurs possibles de la colonne enum `nom`
    const BOUTIQUE = 'boutique';
    const MAGASIN = 'magasin';
 
    protected $fillable = [
        'nom',
    ];
 
    /**
     * Les stocks (quantités produit par produit) de cet emplacement.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
 
    /**
     * Les mouvements dont c'est l'emplacement source/concerné.
     */
    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }
 
    /**
     * Les mouvements de type "transfert" dont c'est la destination.
     */
    public function mouvementsEntrants(): HasMany
    {
        return $this->hasMany(Mouvement::class, 'emplacement_destination_id');
    }
 
    /**
     * Les factures émises depuis cet emplacement.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
 
    /**
     * Les approvisionnements reçus dans cet emplacement.
     */
    public function aprovisionnements(): HasMany
    {
        return $this->hasMany(Aprovisionnement::class);
    }
}
