<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'reset_code',
        'is_active',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

     /**
     * Le rôle de l'utilisateur.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Les produits créés/gérés par cet utilisateur.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Les mouvements de stock effectués par cet utilisateur.
     */
    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }

    /**
     * Les ventes effectuées par cet utilisateur.
     */
    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    /**
     * Les factures créées par cet utilisateur.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Les approvisionnements effectués par cet utilisateur.
     */
    public function aprovisionnements(): HasMany
    {
        return $this->hasMany(Aprovisionnement::class);
    }

    // Helpers pour vérifier le rôle
    public function isAdmin(): bool
    {
        return $this->role?->name === 'Admin';
    }

    public function isSuperadmin(): bool
    {
        return $this->role?->name === 'Superadmin';
    }

    public function isGerant(): bool
    {
        return $this->role?->name === 'Gerant';
    }

    public function isCaisse(): bool
    {
        return $this->role?->name === 'Caisse';
    }

    public function isAccueil(): bool
    {
        return $this->role?->name === 'Accueil';
    }
}
