<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // Affiche le formulaire de création de compte
    public function create()
    {
        return view('users.create');
    }

    // Enregistre un nouvel utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'is_super_admin' => $request->has('is_super_admin') ? 1 : 0, // Gère la case cochée ou non
        ]);
    
        return redirect()->route('users.index')->with('success', 'Compte créé avec succès !');
    }

    // Affiche la liste de tous les utilisateurs
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // Supprime un utilisateur
    public function destroy(User $user)
    {
        // Optionnel : ajouter une vérification pour éviter la suppression du compte admin, par exemple.
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}