<?php

namespace App\Http\Controllers;

use App\Mail\QuoteGenerated;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuoteController extends Controller
{
    /**
     * Affiche la liste des devis.
     */
    public function index(Request $request)
    {
        // Récupérer les devis avec pagination
        $devis = Devis::with(['client', 'user'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('numero', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($query) use ($search) {
                        $query->where('nom', 'like', "%{$search}%");
                    });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('statut', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Statistiques des devis
        $stats = [
            'total' => Devis::count(),
            'en_attente' => Devis::where('statut', 'En attente')->count(),
            'acceptes' => Devis::where('statut', 'Accepté')->count(),
            'refuses' => Devis::where('statut', 'Refusé')->count(),
            'expires' => Devis::where('statut', 'Expiré')->count(),
            'factures' => Devis::where('statut', 'Facturé')->count(),
        ];

        return Inertia::render('Quotes/Index', [
            'devis' => $devis,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Affiche le formulaire de création d'un devis.
     */
    public function create()
    {
        // Récupérer la liste des clients pour le formulaire
        $clients = Client::orderBy('nom')->get();

        // Générer un numéro de devis unique
        $lastDevis = Devis::orderBy('id', 'desc')->first();
        $nextId = $lastDevis ? $lastDevis->id + 1 : 1;
        $devisNumber = 'D-' . date('Ym') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return Inertia::render('Quotes/Create', [
            'clients' => $clients,
            'devisNumber' => $devisNumber,
            'defaultValidityDays' => 30, // Validité par défaut de 30 jours
        ]);
    }

    /**
     * Enregistre un nouveau devis.
     */
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validated = $request->validate([
            'numero' => 'required|string|max:255|unique:devis,numero',
            'date_emission' => 'required|date',
            'date_validite' => 'required|date|after_or_equal:date_emission',
            'client_id' => 'required|exists:clients,id',
            'lignes' => 'required|array|min:1',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'required|numeric|min:0',
            'conditions_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
            'mentions_legales' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculer les totaux
            $totalHT = 0;
            $totalTVA = 0;
            $totalTTC = 0;

            foreach ($validated['lignes'] as $ligne) {
                $montantHT = $ligne['quantite'] * $ligne['prix_unitaire'];
                $montantTVA = $montantHT * ($ligne['taux_tva'] / 100);
                $montantTTC = $montantHT + $montantTVA;

                $totalHT += $montantHT;
                $totalTVA += $montantTVA;
                $totalTTC += $montantTTC;
            }

            // Créer le devis
            $devis = Devis::create([
                'numero' => $validated['numero'],
                'date_emission' => $validated['date_emission'],
                'date_validite' => $validated['date_validite'],
                'statut' => 'En attente',
                'total_ht' => $totalHT,
                'total_tva' => $totalTVA,
                'total_ttc' => $totalTTC,
                'conditions_paiement' => $validated['conditions_paiement'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'mentions_legales' => $validated['mentions_legales'] ?? null,
                'client_id' => $validated['client_id'],
                'user_id' => Auth::id(),
            ]);

            // Créer les lignes du devis
            foreach ($validated['lignes'] as $ligne) {
                $montantHT = $ligne['quantite'] * $ligne['prix_unitaire'];
                $montantTVA = $montantHT * ($ligne['taux_tva'] / 100);
                $montantTTC = $montantHT + $montantTVA;

                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'description' => $ligne['description'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'taux_tva' => $ligne['taux_tva'],
                    'montant_ht' => $montantHT,
                    'montant_tva' => $montantTVA,
                    'montant_ttc' => $montantTTC,
                ]);
            }

            DB::commit();

            // Envoyer le devis par email si demandé
            if ($request->input('send_email', false)) {
                $client = Client::find($validated['client_id']);
                
                if ($client && $client->email) {
                    try {
                        // Utiliser send() au lieu de queue() pour envoyer immédiatement
                        Mail::to($client->email)
                            ->send(new QuoteGenerated($devis->fresh(['client', 'lignes', 'user'])));
                        
                        return redirect()->route('quotes.index')
                            ->with('success', 'Devis créé avec succès et email envoyé au client.');
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de l\'email du devis', [
                            'devis_id' => $devis->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        return redirect()->route('quotes.index')
                            ->with('success', 'Devis créé avec succès, mais l\'email n\'a pas pu être envoyé: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('quotes.index')
                ->with('success', 'Devis créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du devis: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un devis.
     */
    public function show(string $id)
    {
        $devis = Devis::with(['client', 'lignes', 'user'])
            ->findOrFail($id);

        return Inertia::render('Quotes/Show', [
            'devis' => $devis,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un devis.
     */
    public function edit(string $id)
    {
        $devis = Devis::with(['client', 'lignes', 'user'])
            ->findOrFail($id);
            
        $clients = Client::orderBy('nom')->get();

        return Inertia::render('Quotes/Edit', [
            'devis' => $devis,
            'clients' => $clients,
        ]);
    }

    /**
     * Met à jour un devis existant.
     */
    public function update(Request $request, string $id)
    {
        $devis = Devis::findOrFail($id);

        // Valider les données du formulaire
        $validated = $request->validate([
            'numero' => 'required|string|max:255|unique:devis,numero,' . $devis->id,
            'date_emission' => 'required|date',
            'date_validite' => 'required|date|after_or_equal:date_emission',
            'client_id' => 'required|exists:clients,id',
            'statut' => 'required|in:En attente,Accepté,Refusé,Expiré,Facturé',
            'lignes' => 'required|array|min:1',
            'lignes.*.id' => 'nullable|exists:ligne_devis,id',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'required|numeric|min:0',
            'conditions_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
            'mentions_legales' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculer les totaux
            $totalHT = 0;
            $totalTVA = 0;
            $totalTTC = 0;

            foreach ($validated['lignes'] as $ligne) {
                $montantHT = $ligne['quantite'] * $ligne['prix_unitaire'];
                $montantTVA = $montantHT * ($ligne['taux_tva'] / 100);
                $montantTTC = $montantHT + $montantTVA;

                $totalHT += $montantHT;
                $totalTVA += $montantTVA;
                $totalTTC += $montantTTC;
            }

            // Mettre à jour le devis
            $devis->update([
                'numero' => $validated['numero'],
                'date_emission' => $validated['date_emission'],
                'date_validite' => $validated['date_validite'],
                'statut' => $validated['statut'],
                'total_ht' => $totalHT,
                'total_tva' => $totalTVA,
                'total_ttc' => $totalTTC,
                'conditions_paiement' => $validated['conditions_paiement'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'mentions_legales' => $validated['mentions_legales'] ?? null,
                'client_id' => $validated['client_id'],
            ]);

            // Récupérer les IDs des lignes existantes
            $existingLigneIds = $devis->lignes->pluck('id')->toArray();
            $updatedLigneIds = [];

            // Mettre à jour ou créer les lignes
            foreach ($validated['lignes'] as $ligne) {
                $montantHT = $ligne['quantite'] * $ligne['prix_unitaire'];
                $montantTVA = $montantHT * ($ligne['taux_tva'] / 100);
                $montantTTC = $montantHT + $montantTVA;

                if (isset($ligne['id'])) {
                    // Mettre à jour une ligne existante
                    LigneDevis::where('id', $ligne['id'])->update([
                        'description' => $ligne['description'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire' => $ligne['prix_unitaire'],
                        'taux_tva' => $ligne['taux_tva'],
                        'montant_ht' => $montantHT,
                        'montant_tva' => $montantTVA,
                        'montant_ttc' => $montantTTC,
                    ]);
                    $updatedLigneIds[] = $ligne['id'];
                } else {
                    // Créer une nouvelle ligne
                    $newLigne = LigneDevis::create([
                        'devis_id' => $devis->id,
                        'description' => $ligne['description'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire' => $ligne['prix_unitaire'],
                        'taux_tva' => $ligne['taux_tva'],
                        'montant_ht' => $montantHT,
                        'montant_tva' => $montantTVA,
                        'montant_ttc' => $montantTTC,
                    ]);
                    $updatedLigneIds[] = $newLigne->id;
                }
            }

            // Supprimer les lignes qui ne sont plus présentes
            $lignesToDelete = array_diff($existingLigneIds, $updatedLigneIds);
            if (!empty($lignesToDelete)) {
                LigneDevis::whereIn('id', $lignesToDelete)->delete();
            }

            DB::commit();

            // Envoyer le devis par email si demandé
            if ($request->input('send_email', false)) {
                $client = Client::find($validated['client_id']);
                
                if ($client && $client->email) {
                    try {
                        // Utiliser send() au lieu de queue() pour envoyer immédiatement
                        Mail::to($client->email)
                            ->send(new QuoteGenerated($devis->fresh(['client', 'lignes', 'user'])));
                        
                        return redirect()->route('quotes.index')
                            ->with('success', 'Devis mis à jour avec succès et email envoyé au client.');
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de l\'email du devis', [
                            'devis_id' => $devis->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        return redirect()->route('quotes.index')
                            ->with('success', 'Devis mis à jour avec succès, mais l\'email n\'a pas pu être envoyé: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('quotes.index')
                ->with('success', 'Devis mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du devis', [
                'devis_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du devis: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un devis.
     */
    public function destroy(string $id)
    {
        try {
            $devis = Devis::findOrFail($id);
            
            // Vérifier si le devis peut être supprimé
            if ($devis->statut === 'Facturé') {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer un devis qui a déjà été facturé.');
            }
            
            $devis->delete();
            
            return redirect()->route('quotes.index')
                ->with('success', 'Devis supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du devis', [
                'devis_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du devis.');
        }
    }

    /**
     * Génère un PDF du devis.
     */
    public function generatePdf(Devis $devis)
    {
        $devis->load(['client', 'lignes', 'user']);
        
        $pdf = PDF::loadView('pdf.quote', [
            'devis' => $devis
        ]);
        
        return $pdf->stream('Devis_' . $devis->numero . '.pdf');
    }

    /**
     * Télécharge un PDF du devis.
     */
    public function downloadPdf(string $id)
    {
        $devis = Devis::with(['client', 'lignes', 'user'])->findOrFail($id);
        
        $pdf = PDF::loadView('pdf.quote', [
            'devis' => $devis
        ]);
        
        return $pdf->download('Devis_' . $devis->numero . '_' . $devis->date_emission->format('Y-m-d') . '.pdf');
    }

    /**
     * Envoie le devis par email.
     */
    public function sendByEmail(string $id)
    {
        try {
            $devis = Devis::with(['client', 'lignes', 'user'])->findOrFail($id);
            
            if (!$devis->client->email) {
                return redirect()->back()
                    ->with('error', 'Le client n\'a pas d\'adresse email.');
            }
            
            // Utiliser send() au lieu de queue() pour envoyer immédiatement
            Mail::to($devis->client->email)
                ->send(new QuoteGenerated($devis));
            
            return redirect()->back()
                ->with('success', 'Devis envoyé par email avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du devis par email', [
                'devis_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'envoi du devis par email: ' . $e->getMessage());
        }
    }

    /**
     * Marque un devis comme accepté.
     */
    public function markAsAccepted(string $id)
    {
        try {
            $devis = Devis::findOrFail($id);
            $devis->update(['statut' => 'Accepté']);
            
            return redirect()->back()
                ->with('success', 'Devis marqué comme accepté avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du devis comme accepté', [
                'devis_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du marquage du devis comme accepté.');
        }
    }

    /**
     * Marque un devis comme refusé.
     */
    public function markAsRejected(string $id)
    {
        try {
            $devis = Devis::findOrFail($id);
            $devis->update(['statut' => 'Refusé']);
            
            return redirect()->back()
                ->with('success', 'Devis marqué comme refusé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du devis comme refusé', [
                'devis_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du marquage du devis comme refusé.');
        }
    }

    /**
     * Convertit un devis en facture.
     */
    public function convertToInvoice(string $id)
    {
        // Cette méthode sera implémentée ultérieurement
        // Elle créera une facture à partir des données du devis
        return redirect()->back()
            ->with('info', 'La conversion en facture sera implémentée prochainement.');
    }
}
