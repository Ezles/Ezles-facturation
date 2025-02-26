<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'email',
        'adresse',
        'code_postal',
        'ville',
        'telephone',
        'siret',
        'numero_tva',
        'user_id', // Pour lier le client à un utilisateur spécifique
    ];

    /**
     * Obtenir les factures associées à ce client.
     */
    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Obtenir l'utilisateur propriétaire de ce client.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 