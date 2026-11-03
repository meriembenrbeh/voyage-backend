<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Récupérer tous les utilisateurs.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    /**
     * Afficher un utilisateur spécifique.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * Ajouter un nouvel utilisateur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,user',
            'avatar' => 'nullable|string',
            'isActive' => 'boolean'
        ]);

        // Créer un nouvel utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hacher le mot de passe
            'role' => $request->role,
            'avatar' => $request->avatar ?? '',
            'isActive' => $request->isActive ?? false,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => $user,
        ], 201);
    }

    /**
     * Mettre à jour un utilisateur existant.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|required|string|in:admin,user',
            'avatar' => 'nullable|string',
            'isActive' => 'sometimes|boolean'
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        // Mettre à jour les informations de l'utilisateur
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password); // Hacher le mot de passe
        }

        if ($request->has('role')) {
            $user->role = $request->role;
        }

        if ($request->has('avatar')) {
            $user->avatar = $request->avatar ?? '';
        }

        if ($request->has('isActive')) {
            $user->isActive = $request->isActive;
        }

        $user->save();

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès.',
            'user' => $user,
        ], 200);
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.'], 200);
    }
}
