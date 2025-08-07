<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Livre::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string',
            'auteur' => 'required|string',
            'editeur' => 'required|string',
            'stock' => 'required|integer|min:0',
            'bibliotheque' => 'required|string',
        ]);
        $livre = \App\Models\Livre::create($validated);
        return response()->json($livre, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $livre = \App\Models\Livre::find($id);
        if (!$livre) {
            return response()->json(['message' => 'Livre non trouvé'], 404);
        }
        return response()->json($livre);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $livre = \App\Models\Livre::find($id);
        if (!$livre) {
            return response()->json(['message' => 'Livre non trouvé'], 404);
        }
        $validated = $request->validate([
            'titre' => 'string',
            'auteur' => 'string',
            'editeur' => 'string',
            'stock' => 'integer|min:0',
            'bibliotheque' => 'string',
        ]);
        $livre->update($validated);
        return response()->json($livre);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $livre = \App\Models\Livre::find($id);
        if (!$livre) {
            return response()->json(['message' => 'Livre non trouvé'], 404);
        }
        $livre->delete();
        return response()->json(['message' => 'Livre supprimé']);
    }
}
