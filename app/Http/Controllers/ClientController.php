<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::where('user_id', Auth::id())
            ->orderBy('nom')
            ->get();
            
        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'adresse' => 'required|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
            'numero_tva' => 'nullable|string|max:20',
        ]);
        
        // Ajouter l'ID de l'utilisateur connecté
        $validated['user_id'] = Auth::id();
        
        // Créer le client
        $client = Client::create($validated);
        
        // Si la requête est AJAX (depuis le modal), retourner le client créé
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'client' => $client
            ]);
        }
        
        // Sinon, rediriger vers la liste des clients
        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::where('user_id', Auth::id())
            ->findOrFail($id);
            
        return Inertia::render('Clients/Show', [
            'client' => $client
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = Client::where('user_id', Auth::id())
            ->findOrFail($id);
            
        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Valider les données du formulaire
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'adresse' => 'required|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
            'numero_tva' => 'nullable|string|max:20',
        ]);
        
        // Récupérer le client
        $client = Client::where('user_id', Auth::id())
            ->findOrFail($id);
            
        // Mettre à jour le client
        $client->update($validated);
        
        return redirect()->route('clients.index')
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Récupérer le client
        $client = Client::where('user_id', Auth::id())
            ->findOrFail($id);
            
        // Supprimer le client
        $client->delete();
        
        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
