<?php

namespace App\Http\Controllers;

use App\Models\Avurnav;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AvurnavController extends Controller
{
    public function index(Request $request)
    {
        $query = Avurnav::query();

        // Filtrage par année sur la colonne 'date'
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Filtrage par mois sur la colonne 'date'
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        // Filtrage par jour sur la colonne 'date'
        if ($request->filled('day')) {
            $query->whereDay('date', $request->day);
        }

        // Filtrage sur un intervalle de dates (date de début et date de fin)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        
        // Filtre de recherche sur plusieurs colonnes incluant 'navire'
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('avurnav_local', 'LIKE', '%' . $search . '%')
                  ->orWhere('ile', 'LIKE', '%' . $search . '%')
                  ->orWhere('vous_signale', 'LIKE', '%' . $search . '%')
                  ->orWhere('position', 'LIKE', '%' . $search . '%')
                  ->orWhere('navire', 'LIKE', '%' . $search . '%')
                  ->orWhere('type', 'LIKE', '%' . $search . '%')
                  ->orWhere('caracteristiques', 'LIKE', '%' . $search . '%')
                  ->orWhere('zone', 'LIKE', '%' . $search . '%')
                  ->orWhere('contacts', 'LIKE', '%' . $search . '%');
            });
        }

        // Tri par date décroissante
        $avurnavs = $query->orderBy('date', 'desc')->get();

        return view('surveillance.avurnav.index', compact('avurnavs'));
    }

    public function create()
    {
        return view('surveillance.avurnav.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'avurnav_local' => 'required|string',
            'ile' => 'required|string',
            'vous_signale' => 'required|string',
            'position' => 'required|string',
            'navire' => 'required|string',
            'pob' => 'required|integer',
            'type' => 'required|string',
            'caracteristiques' => 'required|string',
            'zone' => 'required|string',
            'derniere_communication' => 'required|date',
            'contacts' => 'required|string',
        ]);

        Avurnav::create($validatedData);

        return redirect()->route('avurnav.index')->with('success', 'Données enregistrées avec succès.');
    }

    public function exportPDF($id)
    {
        $avurnav = Avurnav::findOrFail($id);
        $pdf = Pdf::loadView('surveillance.avurnav.pdf', compact('avurnav'));

        return $pdf->download("AVURNAV_{$avurnav->id}.pdf");
    }
}
