<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero',
        'date_emission',
        'date_echeance',
        'statut',
        'client_id',
        'user_id',
        'conditions_paiement',
        'mode_paiement',
        'notes',
        'mentions_legales',
        'total_ht',
        'total_tva',
        'total_ttc',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    /**
     * Obtenir le client associé à cette facture.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtenir l'utilisateur propriétaire de cette facture.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les lignes de cette facture.
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneFacture::class);
    }

    /**
     * Générer un numéro de facture unique selon la convention.
     * Format: EZLES-YYYYMM-XXX où XXX est un numéro séquentiel
     */
    public static function genererNumero(): string
    {
        $prefix = 'EZLES';
        $date = now()->format('Ym'); // Format YYYYMM
        
        // Trouver la dernière facture du mois en cours
        $dernierNumero = self::where('numero', 'like', "{$prefix}-{$date}-%")
            ->orderBy('numero', 'desc')
            ->value('numero');
        
        if ($dernierNumero) {
            // Extraire le numéro séquentiel et l'incrémenter
            $sequence = (int) substr($dernierNumero, -3);
            $sequence++;
        } else {
            // Première facture du mois
            $sequence = 1;
        }
        
        // Formater le numéro séquentiel sur 3 chiffres
        $sequenceFormatee = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequenceFormatee}";
    }
} 