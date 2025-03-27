<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Affiche le formulaire d'édition des infos de l'utilisateur connecté
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Met à jour les informations de l'utilisateur
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validation des champs de base
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Mise à jour des infos de base
        $data = $request->only('name', 'email');

        // Si l'utilisateur souhaite changer son mot de passe
        if ($request->filled('new_password')) {
            // Validation spécifique pour le mot de passe
            $request->validate([
                'current_password'      => 'required',
                'new_password'          => 'required|string|min:8|confirmed',
            ]);

            // Vérifier que le mot de passe actuel est correct
            if (! Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }

            // Mise à jour du mot de passe
            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Vos informations ont été mises à jour.');
    }
}
