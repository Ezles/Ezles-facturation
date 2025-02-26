<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneFacture extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facture_id',
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    /**
     * Obtenir la facture associée à cette ligne.
     */
    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Calculer le montant HT de cette ligne.
     */
    public function calculerMontantHT(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }

    /**
     * Calculer le montant de TVA de cette ligne.
     */
    public function calculerMontantTVA(): float
    {
        return $this->calculerMontantHT() * ($this->taux_tva / 100);
    }

    /**
     * Calculer le montant TTC de cette ligne.
     */
    public function calculerMontantTTC(): float
    {
        return $this->calculerMontantHT() + $this->calculerMontantTVA();
    }
} 