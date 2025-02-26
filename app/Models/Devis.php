<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devis extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'devis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero',
        'date_emission',
        'date_validite',
        'statut',
        'total_ht',
        'total_tva',
        'total_ttc',
        'conditions_paiement',
        'notes',
        'mentions_legales',
        'client_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_emission' => 'datetime',
        'date_validite' => 'datetime',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    /**
     * Get the client that owns the devis.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that owns the devis.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lignes for the devis.
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneDevis::class);
    }
    
    /**
     * Générer un numéro de devis unique selon la convention.
     * Format: EZLES-DEVIS-YYYYMM-XXX où XXX est un numéro séquentiel
     */
    public static function genererNumero(): string
    {
        $prefix = 'EZLES-DEVIS';
        $date = now()->format('Ym'); // Format YYYYMM
        
        // Trouver le dernier devis du mois en cours
        $dernierNumero = self::where('numero', 'like', "{$prefix}-{$date}-%")
            ->orderBy('numero', 'desc')
            ->value('numero');
        
        if ($dernierNumero) {
            // Extraire le numéro séquentiel et l'incrémenter
            $sequence = (int) substr($dernierNumero, -3);
            $sequence++;
        } else {
            // Premier devis du mois
            $sequence = 1;
        }
        
        // Formater le numéro séquentiel sur 3 chiffres
        $sequenceFormatee = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequenceFormatee}";
    }
}
