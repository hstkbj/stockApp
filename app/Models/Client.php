<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'fullname',
        'email',
        'phone',
        'adresse',
        'ifu',
    ];

    /**
     * Les factures de ce client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
