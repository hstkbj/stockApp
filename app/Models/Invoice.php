<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
     protected $fillable = [
        'invoice_number',
        'status',
        'due_at',
        'echeance_at',
        'total_ht',
        'total_tva',
        'total_ttc',
        'client_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'date',
            'echeance_at' => 'date',
            'total_ht' => 'decimal:2',
            'total_tva' => 'decimal:2',
            'total_ttc' => 'decimal:2',
        ];
    }

    /**
     * Le client concerné par cette facture.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * L'utilisateur ayant créé cette facture.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les lignes de cette facture.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
