<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mouvement extends Model
{
     const TYPE_ENTREE = 'entree';
    const TYPE_SORTIE = 'sortie';
    const TYPE_TRANSFERT = 'transfert';
 
    protected $fillable = [
        'product_id',
        'emplacement_id',
        'emplacement_destination_id',
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
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    /**
     * Emplacement source / concerné (entrée, sortie, ou source d'un transfert).
     */
    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }
 
    /**
     * Emplacement de destination, rempli uniquement si type = transfert.
     */
    public function emplacementDestination(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'emplacement_destination_id');
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function isTransfert(): bool
    {
        return $this->type === self::TYPE_TRANSFERT;
    }
}
