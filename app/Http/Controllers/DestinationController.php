<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * Afficher la liste des destinations.
     */
    public function index()
    {
        try {
            $destinations = Destination::with('voyages')->get();
            return response()->json($destinations);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de récupération des destinations'], 500);
        }
    }

    /**
     * Créer une nouvelle destination.
     */
    public function store(Request $request)
    {
        try {
            $destination = new Destination([
                "nom" => $request->input("nom"),
                "description" => $request->input("description"),
                "image" => $request->input("image"),
            ]);
            $destination->save();
            return response()->json($destination);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Insertion impossible'], 500);
        }
    }

    /**
     * Afficher une destination spécifique.
     */
    public function show($id)
    {
        try {
            $destination = Destination::with('voyages')->findOrFail($id);
            return response()->json($destination);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de récupération des données'], 500);
        }
    }

    /**
     * Mettre à jour une destination spécifique.
     */
    public function update(Request $request, $id)
    {
        try {
            $destination = Destination::findOrFail($id);
            $destination->update($request->all());
            return response()->json($destination);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de modification'], 500);
        }
    }

    /**
     * Supprimer une destination.
     */
    public function destroy($id)
    {
        try {
            $destination = Destination::findOrFail($id);
            $destination->voyages()->delete();
            $destination->delete();
            return response()->json(['message' => 'Destination supprimée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de suppression de destination'], 500);
        }
    }
  // app/Http/Controllers/DestinationController.php
  public function destinationsPaginate()
  {
      try {
          $perPage = request()->input('pageSize', 10); // La taille de page (pageSize)
          $destinations = Destination::paginate($perPage); // Récupère les destinations sans les voyages
          return response()->json([
              'products' => $destinations->items(), // Les éléments de la page actuelle
              'totalPages' => $destinations->lastPage(), // Nombre total de pages
          ]);
      } catch (\Exception $e) {
          return response()->json([
              'message' => 'Selection impossible',
              'error' => $e->getMessage(),
              'trace' => $e->getTraceAsString()
          ]);
      }
  }
  
  
  

}
