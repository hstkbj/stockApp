<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprovisionnementItem extends Model
{
    /**
     * La migration de cette table ne contient pas $table->timestamps(),
     * il faut donc désactiver created_at/updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'aprovisionnement_id',
        'product_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
            'prix_total' => 'decimal:2',
        ];
    }

    /**
     * L'approvisionnement auquel appartient cette ligne.
     */
    public function aprovisionnement(): BelongsTo
    {
        return $this->belongsTo(Aprovisionnement::class);
    }

    /**
     * Le produit concerné par cette ligne.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
