<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'unit_price',
        'vat_rate',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }

    /**
     * Le produit concerné par cette ligne de facture.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * La facture à laquelle appartient cette ligne.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
