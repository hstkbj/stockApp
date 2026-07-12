<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = [
        'product_id', 'emplacement_id', 'status',
        'last_notification_ids', 'last_notified_at', 'resolved_at',
    ];

    protected $casts = [
        'last_notification_ids' => 'array',
        'last_notified_at'      => 'datetime',
        'resolved_at'            => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }
}
