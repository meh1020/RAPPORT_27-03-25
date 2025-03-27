<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    // Affiche le formulaire de changement de mot de passe
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    // Met à jour le mot de passe de l'utilisateur connecté
    public function change(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|confirmed|min:6',
        ]);

        $user = Auth::user();

        // Vérifie le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        // Met à jour le mot de passe
        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return back()->with('status', 'Mot de passe mis à jour avec succès.');
    }
}
