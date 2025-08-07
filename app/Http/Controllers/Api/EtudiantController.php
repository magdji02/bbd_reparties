<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$etudiants = \App\Models\Etudiant::all();
        //return response()->json(['etudiants' => $etudiants]);

        try {
            $etudiants = \App\Models\Etudiant::all();
            return response()->json([
                'status' => 'success',
                'data' => $etudiants // Changez 'etudiants' en 'data' pour plus de cohérence
            ])
            ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_permanent' => 'required|string|unique:etudiants,code_permanent',
            'nom' => 'required|string',
            'universite' => 'required|string',
            'specialite' => 'required|string',
            'nbreEmprunts' => 'integer|min:0',
        ]);
        $etudiant = \App\Models\Etudiant::create($validated);
        return response()->json($etudiant, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $etudiant = \App\Models\Etudiant::find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Etudiant non trouvé'], 404);
        }
        return response()->json($etudiant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $etudiant = \App\Models\Etudiant::find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Etudiant non trouvé'], 404);
        }
        $validated = $request->validate([
            'code_permanent' => 'string|unique:etudiants,code_permanent,' . $id,
            'nom' => 'string',
            'universite' => 'string',
            'specialite' => 'string',
            'nbreEmprunts' => 'integer|min:0',
        ]);
        $etudiant->update($validated);
        return response()->json($etudiant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $etudiant = \App\Models\Etudiant::find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Etudiant non trouvé'], 404);
        }
        $etudiant->delete();
        return response()->json(['message' => 'Etudiant supprimé']);
    }
}
