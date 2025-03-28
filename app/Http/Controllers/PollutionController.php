<?php

namespace App\Http\Controllers;

use App\Models\Pollution;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PollutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Pollution::query();

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
        
        // Filtre de recherche sur plusieurs colonnes : numero, zone, coordonnees et type_pollution
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('numero', 'LIKE', '%' . $search . '%')
                  ->orWhere('zone', 'LIKE', '%' . $search . '%')
                  ->orWhere('coordonnees', 'LIKE', '%' . $search . '%')
                  ->orWhere('type_pollution', 'LIKE', '%' . $search . '%');
            });
        }

        // Tri par date décroissante
        $pollutions = $query->orderBy('date', 'desc')->get();

        return view('surveillance.pollutions.index', compact('pollutions'));
    }

    public function create()
    {
        return view('surveillance.pollutions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'numero' => 'required|string',
            'zone' => 'required|string',
            'coordonnees' => 'required|string',
            'type_pollution' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        $pollution = Pollution::create($request->only(['date','numero', 'zone', 'coordonnees', 'type_pollution']));
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images', 'public');
                $pollution->images()->create(['image_path' => $path]);
            }
        }
    
        return redirect()->route('pollutions.index')->with('success', 'Donnée ajoutée avec succès.');
    }
    
    public function show(Pollution $pollution)
    {
        return view('pollutions.show', compact('pollution'));
    }
    
    public function edit(Pollution $pollution)
    {
        return view('pollutions.edit', compact('pollution'));
    }
    
    public function update(Request $request, Pollution $pollution)
    {
        $request->validate([
            'date' => 'required|date',
            'numero' => 'required|string',
            'zone' => 'required|string',
            'coordonnees' => 'required|string',
            'type_pollution' => 'required|string',
            'image_satellite' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();
        if ($request->hasFile('image_satellite')) {
            $data['image_satellite'] = $request->file('image_satellite')->store('images', 'public');
        }

        $pollution->update($data);

        return redirect()->route('pollutions.index')->with('success', 'Donnée mise à jour.');
    }
    
    public function destroy(Pollution $pollution)
    {
        foreach ($pollution->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
    
        $pollution->delete();
        return redirect()->route('pollutions.index')->with('success', 'Donnée supprimée.');
    }
    
    public function exportPDF($id)
    {
        $pollution = Pollution::findOrFail($id);
        
        $pdf = Pdf::loadView('surveillance.pollutions.pdf', compact('pollution'));
        return $pdf->download("pollution_{$pollution->id}.pdf");
    }
}
