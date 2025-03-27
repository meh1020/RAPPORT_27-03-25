<?php

namespace App\Http\Controllers;

use App\Models\Vedette;
use Illuminate\Http\Request;

class VedetteSar extends Controller
{
    /**
     * Affiche la liste des cabotages (VEDETTE SAR) avec filtrage.
     */
    public function index(Request $request)
    {
        $query = Vedette::query();

        // Filtrage par année sur la date
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Filtrage par mois sur la date
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        // Filtrage par jour sur la date
        if ($request->filled('day')) {
            $query->whereDay('date', $request->day);
        }

        // Filtrage par intervalle de dates (date de début et date de fin)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Recherche sur le champ 'unite_sar'
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('unite_sar', 'LIKE', '%' . $search . '%');
        }

        // Tri par date décroissante et pagination (conservation des paramètres)
        $vedettes = $query->orderBy('date', 'desc')->paginate(50)->appends($request->all());

        return view('surveillance.vedette_sar.index', compact('vedettes'));
    }

    /**
     * Affiche le formulaire de création d'un cabotage.
     */
    public function create()
    {
        return view('surveillance.vedette_sar.create');
    }

    /**
     * Stocke un nouveau cabotage dans la base de données.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date'                => 'required|date',
            'unite_sar'           => 'required|string|max:255',
            'total_interventions' => 'nullable|integer|max:255',
            'total_pob'           => 'nullable|integer',
            'total_survivants'    => 'nullable|integer',
            'total_morts'         => 'nullable|integer',
            'total_disparus'      => 'nullable|integer',
        ]);

        Vedette::create($validatedData);

        return redirect()->route('vedette_sar.index')->with('success', 'Vedette Sar créé avec succès !');
    }

    /**
     * Supprime un cabotage de la base de données.
     */
    public function destroy($id)
    {
        $vedette = Vedette::findOrFail($id);
        $vedette->delete();

        return redirect()->route('vedette_sar.index')->with('success', 'Cabotage supprimé avec succès !');
    }
}
