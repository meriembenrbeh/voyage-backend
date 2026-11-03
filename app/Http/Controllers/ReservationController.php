<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Voyage;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Récupérer toutes les réservations.
     */
    public function index()
    {
        $reservations = Reservation::with(['voyage', 'user'])->get();
        return response()->json($reservations, 200);
    }

    /**
     * Ajouter une nouvelle réservation.
     */
    public function store(Request $request)
    {
        // Accept frontend field `idMatch` as alias for backend `idVoyage`
        if ($request->has('idMatch') && !$request->has('idVoyage')) {
            $request->merge(['idVoyage' => $request->idMatch]);
        }

        // Validation des données entrantes
        $request->validate([
            'idVoyage' => 'required|exists:voyages,id',
            'idUser' => 'required|exists:users,id',
            'nbPlaceAReserver' => 'required|integer|min:1',
        ]);
    
        // Récupérer le voyage sélectionné
        $voyage = Voyage::find($request->idVoyage);
    
        if (!$voyage) {
            return response()->json(['message' => 'Voyage introuvable.'], 404);
        }
    
        // Calculer la somme des places déjà réservées pour ce voyage
        $sommePlacesReservees = Reservation::where('idVoyage', $request->idVoyage)->sum('nbPlaceAReserver');
    
        // Vérifier si le nombre de places disponibles est suffisant
        $nbPlacesRestantes = $voyage->nbplacetotal - $sommePlacesReservees;
    
        if ($request->nbPlaceAReserver > $nbPlacesRestantes) {
            return response()->json([
                'message' => 'Le nombre de places demandées dépasse le nombre de places disponibles.',
                'placesDisponibles' => $nbPlacesRestantes
            ], 400);
        }
    
        // Ajouter la réservation
        $reservation = Reservation::create([
            'idVoyage' => $request->idVoyage,
            'idUser' => $request->idUser,
            'nbPlaceAReserver' => $request->nbPlaceAReserver,
        ]);
    
        return response()->json([
            'message' => 'Réservation créée avec succès.',
            'reservation' => $reservation
        ], 201);
    }
    

    /**
     * Afficher une réservation spécifique.
     */
    public function show($id)
    {
        $reservation = Reservation::with(['voyage', 'user'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable.'], 404);
        }

        return response()->json($reservation, 200);
    }

    /**
     * Mettre à jour une réservation.
     */
    public function update(Request $request, $id)
    {
        // Accept `idMatch` as alias in updates as well
        if ($request->has('idMatch') && !$request->has('idVoyage')) {
            $request->merge(['idVoyage' => $request->idMatch]);
        }

        $request->validate([
            'idVoyage' => 'sometimes|required|exists:voyages,id',
            'idUser' => 'sometimes|required|exists:users,id',
            'nbPlaceAReserver' => 'sometimes|required|integer|min:1',
        ]);

        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable.'], 404);
        }

        $reservation->update($request->only(['idVoyage', 'idUser', 'nbPlaceAReserver']));

        return response()->json([
            'message' => 'Réservation mise à jour avec succès.',
            'reservation' => $reservation
        ], 200);
    }

    /**
     * Supprimer une réservation.
     */
    public function destroy($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable.'], 404);
        }

        $reservation->delete();

        return response()->json(['message' => 'Réservation supprimée avec succès.'], 200);
    }

    /**
     *
     * @param  int  $idVoyage
     * @param  int  $nbPlaceAReserver
     * @return bool
     */
    public function verifNbPlace($idVoyage, $nbPlaceAReserver)
    {
        $voyage = Voyage::find($idVoyage);

        $nbPlacesRestantes = $voyage->nbreDePlaceTotal - $voyage->reservations()->sum('nbPlaceAReserver');

        return $nbPlaceAReserver <= $nbPlacesRestantes;
    }
    public function sommePlaces($idVoyage)
{
    // Vérifier si le voyage existe
    $voyage = Voyage::find($idVoyage);

    if (!$voyage) {
        return response()->json(['message' => 'Voyage introuvable.'], 404);
    }

    // Calculer la somme des places réservées
    $sommePlaces = Reservation::where('idVoyage', $idVoyage)->sum('nbPlaceAReserver');

    return response()->json([
        'message' => 'Somme des places réservées récupérée avec succès.',
        'sommePlaces' => $sommePlaces
    ], 200);
}

}
