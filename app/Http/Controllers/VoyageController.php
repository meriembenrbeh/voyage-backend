<?php

namespace App\Http\Controllers;

use App\Models\Voyage;
use Illuminate\Http\Request;

class VoyageController extends Controller
{
    /**
     * Afficher la liste des voyages.
     */
    public function index()
    {
        try {
            $voyages = Voyage::with('destination')->get();
            return response()->json($voyages);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de récupération de la liste des voyages'], 500);
        }
    }

    /**
     * Créer un nouveau voyage.
     */
    public function store(Request $request)
    {
        try {
            $voyage = new Voyage([
                "datevoyage" => $request->input("datevoyage"),
                "prixplace" => $request->input("prixplace"),
                "nbplacetotal" => $request->input("nbplacetotal"),
                "depart" => $request->input("depart"),
                "destination_id" => $request->input("destination_id")
            ]);
            $voyage->save();
            return response()->json($voyage);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Insertion impossible'], 500);
        }
    }

    /**
     * Afficher un voyage spécifique.
     */
    public function show($id)
    {
        try {
            $voyage = Voyage::with('destination')->findOrFail($id);
            return response()->json($voyage);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de récupération des données'], 500);
        }
    }

    /**
     * Mettre à jour un voyage spécifique.
     */
    public function update(Request $request, $id)
    {
        try {
            $voyage = Voyage::findOrFail($id);
            $voyage->update($request->all());
            return response()->json($voyage);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de modification'], 500);
        }
    }

    /**
     * Supprimer un voyage.
     */
    public function destroy($id)
    {
        try {
            $voyage = Voyage::findOrFail($id);
            $voyage->delete();
            return response()->json(['message' => 'Voyage supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de suppression de voyage'], 500);
        }
    }
    public function voyagesPaginate()
    {
        try {
            $perPage = request()->input('pageSize', 10);
            // Récupère la valeur dynamique pour la pagination
            $Voyages = Voyage::with('destination')->paginate($perPage);
            // Retourne le résultat en format JSON API
            return response()->json([
                'products' => $Voyages->items(), // Les articles paginés
                'totalPages' => $Voyages->lastPage(), // Le nombre de pages
            ]);
            } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
            }
    }
}
