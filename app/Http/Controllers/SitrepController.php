<?php

namespace App\Http\Controllers;

use App\Models\Sitrep;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SitrepController extends Controller
{
    public function index(Request $request)
    {
        $query = Sitrep::query();

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
        
        // Filtre de recherche sur plusieurs colonnes
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('sitrep_sar', 'LIKE', '%' . $search . '%')
                  ->orWhere('mrcc_madagascar', 'LIKE', '%' . $search . '%')
                  ->orWhere('event', 'LIKE', '%' . $search . '%')
                  ->orWhere('situation', 'LIKE', '%' . $search . '%')
                  ->orWhere('number_of_persons', 'LIKE', '%' . $search . '%')
                  ->orWhere('assistance_required', 'LIKE', '%' . $search . '%')
                  ->orWhere('coordinating_rcc', 'LIKE', '%' . $search . '%')
                  ->orWhere('initial_action_taken', 'LIKE', '%' . $search . '%')
                  ->orWhere('chronology', 'LIKE', '%' . $search . '%')
                  ->orWhere('additional_information', 'LIKE', '%' . $search . '%');
            });
        }

        // Tri par date décroissante (pagination ou get() selon vos besoins)
        $sitreps = $query->orderBy('date', 'desc')->get();

        return view('surveillance.sitreps.index', compact('sitreps'));
    }

    public function create()
    {
        return view('surveillance.sitreps.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'date',
            'sitrep_sar' => 'required|string',
            'mrcc_madagascar' => 'required|string',
            'event' => 'required|string',
            'position' => 'required|string',
            'situation' => 'required|string',
            'number_of_persons' => 'required|string',
            'assistance_required' => 'required|string',
            'coordinating_rcc' => 'required|string',
            'initial_action_taken' => 'required|string',
            'chronology' => 'required|string',
            'additional_information' => 'nullable|string',
        ]);

        Sitrep::create($request->all());
        return redirect()->route('sitreps.index')->with('success', 'SITREP ajouté avec succès.');
    }

    public function show(Sitrep $sitrep)
    {
        return view('sitreps.show', compact('sitrep'));
    }

    public function edit(Sitrep $sitrep)
    {
        return view('surveillance.sitreps.edit', compact('sitrep'));
    }

    public function update(Request $request, Sitrep $sitrep)
    {
        $request->validate([
            'sitrep_sar' => 'required|string',
            'mrcc_madagascar' => 'required|string',
            'event' => 'required|string',
            'position' => 'required|string',
            'situation' => 'required|string',
            'number_of_persons' => 'required|string',
            'assistance_required' => 'required|string',
            'coordinating_rcc' => 'required|string',
            'initial_action_taken' => 'required|string',
            'chronology' => 'required|string',
            'additional_information' => 'nullable|string',
        ]);

        $sitrep->update($request->all());
        return redirect()->route('sitreps.index')->with('success', 'SITREP mis à jour avec succès.');
    }

    public function destroy(Sitrep $sitrep)
    {
        $sitrep->delete();
        return redirect()->route('sitreps.index')->with('success', 'SITREP supprimé avec succès.');
    }

    public function exportPDF($id)
    {
        $sitrep = Sitrep::findOrFail($id);
        $pdf = Pdf::loadView('surveillance.sitreps.pdf', compact('sitrep'));
        return $pdf->download('sitrep_' . $id . '.pdf');
    }
}
