<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmpruntController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Emprunt::with(['etudiant', 'livre'])->get());
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'livre_id' => 'required|exists:livres,id',
            'date_emprunt' => 'required|date',
            'date_retour' => 'required|date|after_or_equal:date_emprunt',
            'statut' => 'required|string',
        ]);
        $emprunt = \App\Models\Emprunt::create($validated);
        return response()->json($emprunt->load(['etudiant', 'livre']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $emprunt = \App\Models\Emprunt::with(['etudiant', 'livre'])->find($id);
        if (!$emprunt) {
            return response()->json(['message' => 'Emprunt non trouvé'], 404);
        }
        return response()->json($emprunt);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $emprunt = \App\Models\Emprunt::find($id);
        if (!$emprunt) {
            return response()->json(['message' => 'Emprunt non trouvé'], 404);
        }
        $validated = $request->validate([
            'etudiant_id' => 'exists:etudiants,id',
            'livre_id' => 'exists:livres,id',
            'date_emprunt' => 'date',
            'date_retour' => 'date|after_or_equal:date_emprunt',
            'statut' => 'string',
        ]);
        $emprunt->update($validated);
        return response()->json($emprunt->load(['etudiant', 'livre']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $emprunt = \App\Models\Emprunt::find($id);
        if (!$emprunt) {
            return response()->json(['message' => 'Emprunt non trouvé'], 404);
        }
        $emprunt->delete();
        return response()->json(['message' => 'Emprunt supprimé']);
    }
}
