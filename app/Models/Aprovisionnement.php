<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aprovisionnement extends Model
{
    const STATUS_LIVRER = 'livrer';
    const STATUS_EN_ATTENTE = 'enAttente';
    const STATUS_BROUILLON = 'brouillon';
 
    protected $fillable = [
        'reference',
        'fournisseur_id',
        'emplacement_id',
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
 
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }
 
    /**
     * L'emplacement (boutique/magasin) où arrive la marchandise.
     */
    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function items(): HasMany
    {
        return $this->hasMany(AprovisionnementItem::class);
    }
}
