<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rayon extends Model
{
    protected $fillable = [
        'nom',
        'category_id',
    ];
 
    /**
     * La catégorie à laquelle appartient ce rayon.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
 
    /**
     * Les produits rangés dans ce rayon.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
