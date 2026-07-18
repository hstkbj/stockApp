<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'anonymous_customer_name',
        'client_id',
        'emplacement_id',
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
     * Le client (peut être null si vente à un client anonyme,
     * voir `anonymous_customer_name`).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * L'emplacement (boutique/magasin) d'où sort la marchandise vendue.
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
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Nom du client à afficher, qu'il soit enregistré ou anonyme.
     */
    public function getNomClientAttribute(): string
    {
        return $this->client?->fullname ?? $this->anonymous_customer_name ?? 'Client anonyme';
    }

    public function mecef(): HasOne
    {
        return $this->hasOne(InvoiceMecef::class)->latestOfMany('created_at');
    }
}
