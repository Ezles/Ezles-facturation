<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Facture;
use App\Models\LigneFacture;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceGenerated;
use App\Mail\InvoicePaid;

class InvoiceController extends Controller
{
    /**
     * Check and update overdue invoices.
     * This method is called from the dashboard to update invoice statuses.
     */
    private function checkOverdueInvoices()
    {
        // Récupérer les factures en attente dont la date d'échéance est passée
        $overdueInvoices = Facture::where('user_id', Auth::id())
            ->where('statut', 'En attente')
            ->where('date_echeance', '<', now()->format('Y-m-d'))
            ->get();
        
        // Mettre à jour le statut des factures en retard
        foreach ($overdueInvoices as $facture) {
            $facture->statut = 'En retard';
            $facture->save();
        }
        
        return $overdueInvoices->count();
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        // Vérifier et mettre à jour les factures en retard
        $overdueCount = $this->checkOverdueInvoices();
        
        // Récupérer les factures de l'utilisateur connecté
        $facturesQuery = Facture::with('client')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
        
        // Filtrer par statut si demandé
        if ($request->has('statut') && $request->statut !== 'all') {
            $facturesQuery->where('statut', $request->statut);
        }
        
        $factures = $facturesQuery->get();
        
        // Formater les données pour l'affichage
        $invoices = $factures->map(function ($facture) {
            return [
                'id' => $facture->numero,
                'client' => $facture->client->nom,
                'date' => $facture->date_emission->format('d/m/Y'),
                'amount' => number_format($facture->total_ttc, 2, ',', ' ') . ' €',
                'status' => $facture->statut
            ];
        });
        
        // Calculer les statistiques
        $total = $factures->sum('total_ttc');
        $paid = $factures->where('statut', 'Payée')->sum('total_ttc');
        $pending = $factures->whereIn('statut', ['En attente', 'En retard'])->sum('total_ttc');
        $paidCount = $factures->where('statut', 'Payée')->count();
        $pendingCount = $factures->whereIn('statut', ['En attente', 'En retard'])->count();
        
        $statistics = [
            'total' => number_format($total, 2, ',', ' '),
            'paid' => number_format($paid, 2, ',', ' '),
            'pending' => number_format($pending, 2, ',', ' '),
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount
        ];
        
        return Inertia::render('Dashboard', [
            'invoices' => $invoices,
            'statistics' => $statistics,
            'overdueCount' => $overdueCount,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'overdue' => $overdueCount > 0 ? "{$overdueCount} facture(s) sont passées en retard." : null
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Générer un numéro de facture unique au format EZLES-YYYYMM-XXX
        $today = now();
        $year = $today->format('Y');
        $month = $today->format('m');
        
        // Trouver le dernier numéro de facture pour ce mois
        $lastInvoice = Facture::where('numero', 'like', "EZLES-{$year}{$month}-%")
            ->orderBy('numero', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastInvoice) {
            // Extraire le numéro de séquence du dernier numéro de facture
            $parts = explode('-', $lastInvoice->numero);
            $sequence = (int)end($parts) + 1;
        }
        
        $numeroFacture = "EZLES-{$year}{$month}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        // Récupérer la liste des clients pour l'auto-complétion
        $clients = Client::orderBy('nom')->get();
        
        // Informations du prestataire (à personnaliser selon vos besoins)
        $prestataire = [
            'nom' => 'Ezles',
            'adresse' => '123 Rue de la Programmation',
            'code_postal' => '75000',
            'ville' => 'Paris',
            'telephone' => '01 23 45 67 89',
            'email' => 'contact@ezles.fr',
            'siret' => '123 456 789 00012',
            'numero_tva' => 'FR 12 345678900',
            'site_web' => 'www.ezles.fr',
            'rib' => 'FR76 1234 5678 9012 3456 7890 123',
        ];
        
        return Inertia::render('invoices/Create', [
            'numeroFacture' => $numeroFacture,
            'clients' => $clients,
            'prestataire' => $prestataire,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validated = $request->validate([
            'numero' => 'required|string|max:255|unique:factures,numero',
            'date_emission' => 'required|date',
            'date_echeance' => 'required|date',
            'statut' => 'required|string|in:Payée,En attente,En retard',
            'client_nom' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_adresse' => 'required|string',
            'client_code_postal' => 'nullable|string|max:10',
            'client_ville' => 'nullable|string|max:255',
            'client_telephone' => 'nullable|string|max:20',
            'client_siret' => 'nullable|string|max:20',
            'client_tva' => 'nullable|string|max:20',
            'prestations' => 'required|array|min:1',
            'prestations.*.description' => 'required|string',
            'prestations.*.quantite' => 'required|numeric|min:0.01',
            'prestations.*.prix_unitaire' => 'required|numeric|min:0',
            'prestations.*.tva' => 'required|numeric|min:0|max:100',
            'conditions_paiement' => 'nullable|string',
            'mode_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
            'mentions_legales' => 'nullable|string',
        ]);

        // Rechercher ou créer le client
        $client = Client::firstOrCreate(
            ['email' => $validated['client_email'], 'nom' => $validated['client_nom']],
            [
                'adresse' => $validated['client_adresse'],
                'code_postal' => $validated['client_code_postal'],
                'ville' => $validated['client_ville'],
                'telephone' => $validated['client_telephone'],
                'siret' => $validated['client_siret'],
                'numero_tva' => $validated['client_tva'],
                'user_id' => auth()->id(),
            ]
        );

        // Calculer les totaux
        $total_ht = 0;
        $total_tva = 0;
        $total_ttc = 0;

        foreach ($validated['prestations'] as $prestation) {
            $ligne_ht = $prestation['quantite'] * $prestation['prix_unitaire'];
            $ligne_tva = $ligne_ht * ($prestation['tva'] / 100);
            
            $total_ht += $ligne_ht;
            $total_tva += $ligne_tva;
        }

        $total_ttc = $total_ht + $total_tva;

        // Créer la facture
        $facture = new Facture();
        $facture->numero = $validated['numero'];
        $facture->date_emission = $validated['date_emission'];
        $facture->date_echeance = $validated['date_echeance'];
        $facture->statut = $validated['statut'];
        $facture->total_ht = $total_ht;
        $facture->total_tva = $total_tva;
        $facture->total_ttc = $total_ttc;
        $facture->conditions_paiement = $validated['conditions_paiement'];
        $facture->mode_paiement = $validated['mode_paiement'];
        $facture->notes = $validated['notes'];
        $facture->mentions_legales = $validated['mentions_legales'];
        $facture->client_id = $client->id;
        $facture->user_id = auth()->id();
        $facture->save();

        // Créer les lignes de facture
        foreach ($validated['prestations'] as $prestation) {
            $ligne_ht = $prestation['quantite'] * $prestation['prix_unitaire'];
            $ligne_tva = $ligne_ht * ($prestation['tva'] / 100);
            $ligne_ttc = $ligne_ht + $ligne_tva;

            $ligneFacture = new LigneFacture();
            $ligneFacture->facture_id = $facture->id;
            $ligneFacture->description = $prestation['description'];
            $ligneFacture->quantite = $prestation['quantite'];
            $ligneFacture->prix_unitaire = $prestation['prix_unitaire'];
            $ligneFacture->taux_tva = $prestation['tva'];
            $ligneFacture->montant_ht = $ligne_ht;
            $ligneFacture->montant_tva = $ligne_tva;
            $ligneFacture->montant_ttc = $ligne_ttc;
            $ligneFacture->save();
        }

        // Message de succès par défaut
        $successMessage = 'Facture créée avec succès !';

        // Envoyer un email au client avec la facture en pièce jointe (en file d'attente)
        if ($client->email) {
            try {
                // Charger la facture avec ses relations pour l'email
                $facture->load(['client', 'lignes', 'user']);
                
                // Envoyer l'email directement au lieu de le mettre en file d'attente
                Mail::to($client->email)
                    ->send(new InvoiceGenerated($facture));
                
                // Ajouter un message de succès pour l'envoi de l'email
                $successMessage = 'Facture créée avec succès ! Un email a été envoyé au client.';
            } catch (\Exception $e) {
                // En cas d'erreur d'envoi d'email, journaliser l'erreur
                Log::error('Erreur lors de l\'envoi de l\'email de facture', [
                    'facture_id' => $facture->id,
                    'client_email' => $client->email,
                    'error' => $e->getMessage()
                ]);
                
                // Message de succès sans mention de l'email
                $successMessage = 'Facture créée avec succès ! L\'envoi de l\'email a échoué.';
            }
        } else {
            // Si le client n'a pas d'email
            $successMessage = 'Facture créée avec succès ! Aucun email n\'a été envoyé (adresse email du client non renseignée).';
        }

        // Rediriger vers le tableau de bord avec un message de succès
        return redirect()->route('dashboard')->with('success', $successMessage);
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id)
    {
        // Récupérer la facture par son numéro
        $facture = Facture::with(['client', 'lignes'])
            ->where('user_id', Auth::id())
            ->where('numero', $id)
            ->firstOrFail();
        
        // Formater les données pour l'affichage
        $invoice = [
            'id' => $facture->numero,
            'client_name' => $facture->client->nom,
            'client_email' => $facture->client->email,
            'client_address' => $facture->client->adresse . "\n" . 
                                ($facture->client->code_postal ? $facture->client->code_postal . ' ' : '') . 
                                ($facture->client->ville ?? ''),
            'client_siret' => $facture->client->siret,
            'client_tva' => $facture->client->numero_tva,
            'invoice_date' => $facture->date_emission->format('Y-m-d'),
            'due_date' => $facture->date_echeance->format('Y-m-d'),
            'status' => $facture->statut,
            'items' => $facture->lignes->map(function ($ligne) {
                return [
                    'description' => $ligne->description,
                    'quantity' => $ligne->quantite,
                    'price' => $ligne->prix_unitaire,
                    'tva' => $ligne->taux_tva
                ];
            }),
            'notes' => $facture->notes,
            'terms' => $facture->conditions_paiement,
            'payment_method' => $facture->mode_paiement,
            'legal_notice' => $facture->mentions_legales,
            'total_ht' => $facture->total_ht,
            'total_tva' => $facture->total_tva,
            'total_ttc' => $facture->total_ttc
        ];
        
        return Inertia::render('invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(string $id)
    {
        // Récupérer la facture par son numéro
        $facture = Facture::with(['client', 'lignes'])
            ->where('user_id', Auth::id())
            ->where('numero', $id)
            ->firstOrFail();
        
        // Récupérer la liste des clients pour l'autocomplétion
        $clients = Client::where('user_id', Auth::id())
            ->select('id', 'nom', 'email', 'adresse', 'code_postal', 'ville', 'telephone', 'siret', 'numero_tva')
            ->get();
        
        // Formater les données pour l'affichage
        $invoice = [
            'id' => $facture->numero,
            'client_name' => $facture->client->nom,
            'client_email' => $facture->client->email,
            'client_address' => $facture->client->adresse,
            'client_postal_code' => $facture->client->code_postal,
            'client_city' => $facture->client->ville,
            'client_phone' => $facture->client->telephone,
            'client_siret' => $facture->client->siret,
            'client_tva' => $facture->client->numero_tva,
            'invoice_date' => $facture->date_emission->format('Y-m-d'),
            'due_date' => $facture->date_echeance->format('Y-m-d'),
            'status' => $facture->statut,
            'items' => $facture->lignes->map(function ($ligne) {
                return [
                    'description' => $ligne->description,
                    'quantity' => $ligne->quantite,
                    'price' => $ligne->prix_unitaire,
                    'tva' => $ligne->taux_tva
                ];
            }),
            'notes' => $facture->notes,
            'terms' => $facture->conditions_paiement,
            'payment_method' => $facture->mode_paiement,
            'legal_notice' => $facture->mentions_legales
        ];
        
        return Inertia::render('invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients
        ]);
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Récupérer la facture par son numéro
            $facture = Facture::where('user_id', Auth::id())
                ->where('numero', $id)
                ->firstOrFail();
            
            // Valider les données de base
            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'client_email' => 'nullable|email|max:255',
                'client_address' => 'required|string',
                'client_postal_code' => 'nullable|string|max:10',
                'client_city' => 'nullable|string|max:255',
                'client_phone' => 'nullable|string|max:20',
                'client_siret' => 'nullable|string|max:20',
                'client_tva' => 'nullable|string|max:20',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date',
                'status' => 'required|string|in:Payée,En attente,En retard',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.tva' => 'required|numeric|min:0|max:100',
                'notes' => 'nullable|string',
                'terms' => 'nullable|string',
                'payment_method' => 'required|string',
                'legal_notice' => 'nullable|string',
            ]);
            
            DB::beginTransaction();
            
            // Mettre à jour le client
            $client = Client::find($facture->client_id);
            $client->nom = $validated['client_name'];
            $client->email = $validated['client_email'] ?? null;
            $client->adresse = $validated['client_address'];
            $client->code_postal = $validated['client_postal_code'] ?? null;
            $client->ville = $validated['client_city'] ?? null;
            $client->telephone = $validated['client_phone'] ?? null;
            $client->siret = $validated['client_siret'] ?? null;
            $client->numero_tva = $validated['client_tva'] ?? null;
            $client->save();
            
            // Calculer les totaux
            $totalHT = 0;
            $totalTVA = 0;
            
            foreach ($validated['items'] as $item) {
                $montantHT = $item['quantity'] * $item['price'];
                $totalHT += $montantHT;
                $totalTVA += $montantHT * ($item['tva'] / 100);
            }
            
            $totalTTC = $totalHT + $totalTVA;
            
            // Mettre à jour la facture
            $facture->date_emission = $validated['invoice_date'];
            $facture->date_echeance = $validated['due_date'];
            $facture->statut = $validated['status'];
            $facture->conditions_paiement = $validated['terms'] ?? null;
            $facture->mode_paiement = $validated['payment_method'];
            $facture->notes = $validated['notes'] ?? null;
            $facture->mentions_legales = $validated['legal_notice'] ?? null;
            $facture->total_ht = $totalHT;
            $facture->total_tva = $totalTVA;
            $facture->total_ttc = $totalTTC;
            $facture->save();
            
            // Supprimer les anciennes lignes de facture
            LigneFacture::where('facture_id', $facture->id)->delete();
            
            // Créer les nouvelles lignes de facture
            foreach ($validated['items'] as $item) {
                $montantHT = $item['quantity'] * $item['price'];
                $montantTVA = $montantHT * ($item['tva'] / 100);
                $montantTTC = $montantHT + $montantTVA;
                
                $ligne = new LigneFacture();
                $ligne->facture_id = $facture->id;
                $ligne->description = $item['description'];
                $ligne->quantite = $item['quantity'];
                $ligne->prix_unitaire = $item['price'];
                $ligne->taux_tva = $item['tva'];
                $ligne->montant_ht = $montantHT;
                $ligne->montant_tva = $montantTVA;
                $ligne->montant_ttc = $montantTTC;
                $ligne->save();
            }
            
            DB::commit();
            
            // Message de succès par défaut
            $successMessage = 'Facture mise à jour avec succès !';
            
            // Envoyer un email au client avec la facture mise à jour en pièce jointe (en file d'attente)
            if ($client->email) {
                try {
                    // Charger la facture avec ses relations pour l'email
                    $facture->load(['client', 'lignes', 'user']);
                    
                    // Envoyer l'email directement au lieu de le mettre en file d'attente
                    Mail::to($client->email)
                        ->send(new InvoiceGenerated($facture));
                    
                    // Ajouter un message de succès pour l'envoi de l'email
                    $successMessage = 'Facture mise à jour avec succès ! Un email a été envoyé au client.';
                } catch (\Exception $e) {
                    // En cas d'erreur d'envoi d'email, journaliser l'erreur
                    Log::error('Erreur lors de l\'envoi de l\'email de facture mise à jour', [
                        'facture_id' => $facture->id,
                        'client_email' => $client->email,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Message de succès sans mention de l'email
                    $successMessage = 'Facture mise à jour avec succès ! L\'envoi de l\'email a échoué.';
                }
            }
            
            // Rediriger vers le tableau de bord avec un message de succès
            return redirect()->route('dashboard')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // En cas d'erreur, journaliser l'erreur et rediriger avec un message d'erreur
            Log::error('Erreur lors de la mise à jour de la facture', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de la mise à jour de la facture. Veuillez réessayer.'])->withInput();
        }
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Récupérer la facture par son numéro
            $facture = Facture::where('user_id', Auth::id())
                ->where('numero', $id)
                ->firstOrFail();
            
            // Supprimer la facture (les lignes seront supprimées automatiquement grâce à la contrainte onDelete('cascade'))
            $facture->delete();
            
            // Rediriger vers le tableau de bord avec un message de succès
            return redirect()->route('dashboard')->with('success', 'Facture supprimée avec succès !');
        } catch (\Exception $e) {
            // En cas d'erreur, journaliser l'erreur et rediriger avec un message d'erreur
            Log::error('Erreur lors de la suppression de la facture', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de la suppression de la facture. Veuillez réessayer.']);
        }
    }

    /**
     * Génère un PDF pour une facture spécifique
     */
    public function generatePdf(Facture $facture)
    {
        // Charger les relations nécessaires
        $facture->load(['client', 'lignes', 'user']);

        // Générer le PDF
        $pdf = PDF::loadView('pdf.invoice', [
            'facture' => $facture
        ]);

        // Optimiser le PDF pour qu'il tienne sur une seule page
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        // Format professionnel pour le nom du fichier
        $fileName = 'Facture_' . $facture->numero . '_' . $facture->date_emission->format('Y-m-d') . '.pdf';

        // Télécharger le PDF
        return $pdf->download($fileName);
    }
    
    /**
     * Export invoices to Excel.
     */
    public function export()
    {
        // Récupérer les factures de l'utilisateur connecté
        $factures = Facture::with('client')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Définir les en-têtes HTTP pour forcer le téléchargement
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="factures_' . date('Y-m-d_His') . '.csv"',
        ];
        
        // Créer une réponse de type stream pour éviter de stocker le fichier
        $callback = function() use ($factures) {
            // Ouvrir le flux de sortie
            $file = fopen('php://output', 'w');
            
            // Écrire l'en-tête UTF-8 BOM pour Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Écrire l'en-tête du CSV
            fputcsv($file, [
                'Numéro de facture',
                'Client',
                'Date d\'émission',
                'Date d\'échéance',
                'Statut',
                'Montant HT',
                'Montant TVA',
                'Montant TTC'
            ], ';');
            
            // Écrire les données des factures
            foreach ($factures as $facture) {
                fputcsv($file, [
                    $facture->numero,
                    $facture->client->nom,
                    $facture->date_emission->format('d/m/Y'),
                    $facture->date_echeance->format('d/m/Y'),
                    $facture->statut,
                    number_format($facture->total_ht, 2, ',', ' '),
                    number_format($facture->total_tva, 2, ',', ' '),
                    number_format($facture->total_ttc, 2, ',', ' ')
                ], ';');
            }
            
            // Fermer le fichier
            fclose($file);
        };
        
        // Retourner la réponse en streaming
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(string $id)
    {
        try {
            // Récupérer la facture par son numéro
            $facture = Facture::where('user_id', Auth::id())
                ->where('numero', $id)
                ->firstOrFail();
            
            // Mettre à jour le statut de la facture
            $facture->statut = 'Payée';
            $facture->save();
            
            // Message de succès par défaut
            $successMessage = 'La facture a été marquée comme payée avec succès !';
            
            // Envoyer un email de reçu de paiement au client
            if ($facture->client->email) {
                try {
                    // Charger la facture avec ses relations pour l'email
                    $facture->load(['client', 'lignes', 'user']);
                    
                    // Envoyer l'email directement au lieu de le mettre en file d'attente
                    Mail::to($facture->client->email)
                        ->send(new InvoicePaid($facture));
                    
                    // Ajouter un message de succès pour l'envoi de l'email
                    $successMessage = 'La facture a été marquée comme payée avec succès ! Un reçu de paiement a été envoyé au client.';
                } catch (\Exception $e) {
                    // En cas d'erreur d'envoi d'email, journaliser l'erreur
                    Log::error('Erreur lors de l\'envoi de l\'email de reçu de paiement', [
                        'facture_id' => $facture->id,
                        'client_email' => $facture->client->email,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Message de succès sans mention de l'email
                    $successMessage = 'La facture a été marquée comme payée avec succès ! L\'envoi du reçu de paiement a échoué.';
                }
            }
            
            // Rediriger vers le tableau de bord avec un message de succès
            return redirect()->route('dashboard')->with('success', $successMessage);
        } catch (\Exception $e) {
            // En cas d'erreur, journaliser l'erreur et rediriger avec un message d'erreur
            Log::error('Erreur lors du marquage de la facture comme payée', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors du marquage de la facture comme payée. Veuillez réessayer.']);
        }
    }

    /**
     * Export unpaid invoices to Excel.
     */
    public function exportUnpaid()
    {
        // Récupérer les factures impayées de l'utilisateur connecté
        $factures = Facture::with('client')
            ->where('user_id', Auth::id())
            ->whereIn('statut', ['En attente', 'En retard'])
            ->orderBy('date_echeance', 'asc')
            ->get();
        
        // Définir les en-têtes HTTP pour forcer le téléchargement
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="factures_impayees_' . date('Y-m-d_His') . '.csv"',
        ];
        
        // Créer une réponse de type stream pour éviter de stocker le fichier
        $callback = function() use ($factures) {
            // Ouvrir le flux de sortie
            $file = fopen('php://output', 'w');
            
            // Écrire l'en-tête UTF-8 BOM pour Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Écrire l'en-tête du CSV
            fputcsv($file, [
                'Numéro de facture',
                'Client',
                'Date d\'émission',
                'Date d\'échéance',
                'Statut',
                'Montant HT',
                'Montant TVA',
                'Montant TTC',
                'Jours de retard'
            ], ';');
            
            // Écrire les données des factures
            foreach ($factures as $facture) {
                // Calculer le nombre de jours de retard
                $dateEcheance = $facture->date_echeance;
                $aujourdHui = now();
                $joursRetard = max(0, $aujourdHui->diffInDays($dateEcheance, false));
                
                fputcsv($file, [
                    $facture->numero,
                    $facture->client->nom,
                    $facture->date_emission->format('d/m/Y'),
                    $facture->date_echeance->format('d/m/Y'),
                    $facture->statut,
                    number_format($facture->total_ht, 2, ',', ' '),
                    number_format($facture->total_tva, 2, ',', ' '),
                    number_format($facture->total_ttc, 2, ',', ' '),
                    $joursRetard
                ], ';');
            }
            
            // Fermer le fichier
            fclose($file);
        };
        
        // Retourner la réponse en streaming
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send an invoice by email.
     */
    public function sendByEmail(string $id)
    {
        try {
            // Récupérer la facture par son numéro
            $facture = Facture::with(['client', 'lignes', 'user'])
                ->where('user_id', Auth::id())
                ->where('numero', $id)
                ->firstOrFail();
            
            // Vérifier si le client a une adresse email
            if (!$facture->client->email) {
                return response()->json(['error' => 'Le client n\'a pas d\'adresse email. Veuillez d\'abord ajouter une adresse email au client.'], 422);
            }
            
            // Envoyer l'email directement pour un retour immédiat à l'utilisateur
            Mail::to($facture->client->email)
                ->send(new InvoiceGenerated($facture));
            
            // Journaliser le succès
            Log::info('Email de facture envoyé avec succès', [
                'facture_id' => $facture->id,
                'client_email' => $facture->client->email
            ]);
            
            // Retourner un message de succès
            return response()->json(['success' => 'La facture a été envoyée par email avec succès !']);
        } catch (\Exception $e) {
            // En cas d'erreur, journaliser l'erreur et retourner un message d'erreur
            Log::error('Erreur lors de l\'envoi de l\'email de facture', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Une erreur est survenue lors de l\'envoi de la facture par email : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate a PDF for the specified invoice.
     */
    public function downloadPdf(string $id)
    {
        // Récupérer la facture par son numéro
        $facture = Facture::with(['client', 'lignes', 'user'])
            ->where('user_id', Auth::id())
            ->where('numero', $id)
            ->firstOrFail();

        // Générer le PDF
        $pdf = PDF::loadView('pdf.invoice', [
            'facture' => $facture
        ]);

        // Optimiser le PDF pour qu'il tienne sur une seule page
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        // Format professionnel pour le nom du fichier
        $fileName = 'Facture_' . $facture->numero . '_' . $facture->date_emission->format('Y-m-d') . '.pdf';

        // Télécharger le PDF
        return $pdf->download($fileName);
    }
}
