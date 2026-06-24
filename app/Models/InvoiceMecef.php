<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceMecef extends Model
{
    protected $table = 'invoice_mecefs';

    protected $fillable = [
        'invoice_id',
        'uid',
        'invoice_type',
        'payment_type',
        'code_mecef_dgi',
        'qr_code',
        'nim',
        'counters',
        'mecef_datetime',
        'total_mecef',
        'vat_b',
        'ht_b',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'mecef_datetime' => 'datetime',
            'total_mecef'    => 'integer',
            'vat_b'          => 'integer',
            'ht_b'           => 'integer',
        ];
    }

    /**
     * La facture liée à cet enregistrement MECeF.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Vérifie si la facture est confirmée (normalisée).
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Vérifie si la facture est annulée côté MECeF.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Vérifie si la facture est en attente de finalisation.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Retourne le label lisible du type de facture.
     */
    public function getInvoiceTypeLabelAttribute(): string
    {
        return match ($this->invoice_type) {
            'FV' => 'Facture de vente',
            'FA' => "Facture d'avoir",
            'EV' => "Facture de vente à l'exportation",
            'EA' => "Facture d'avoir à l'exportation",
            default => $this->invoice_type,
        };
    }

    /**
     * Retourne le label lisible du type de paiement.
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'ESPECES'      => 'Espèces',
            'VIREMENT'     => 'Virement',
            'CARTEBANCAIRE'=> 'Carte bancaire',
            'MOBILEMONEY'  => 'Mobile Money',
            'CHEQUES'      => 'Chèques',
            'CREDIT'       => 'Crédit',
            'AUTRE'        => 'Autre',
            default        => $this->payment_type,
        };
    }

    /**
     * Retourne le badge de statut (pour affichage direct en vue).
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'Normalisée',
            'cancelled' => 'Annulée',
            'pending'   => 'En attente',
            default     => $this->status,
        };
    }
}
