<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Destination;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
    
        if ($query) {
            // Filtre les ports dont le nom contient la chaîne recherchée
            $ports = Port::with('destinations')
                         ->where('name', 'LIKE', '%' . $query . '%')
                         ->get();
        } else {
            $ports = Port::with('destinations')->get();
        }
    
        return view('ports.index', compact('ports'));
    }

    public function create()
    {
        $destinations = Destination::orderBy('name', 'asc')->get();
        return view('ports.create', compact('destinations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ports,name',
            'destinations' => 'array',
        ]);

        $port = Port::create(['name' => $request->name]);
        $port->destinations()->sync($request->destinations);

        return redirect()->route('ports.index')->with('success', 'Port ajouté avec succès.');
    }

    public function edit(Port $port)
    {
        $destinations = Destination::orderBy('name', 'asc')->get();
        return view('ports.edit', compact('port', 'destinations'));
    }

    public function update(Request $request, Port $port)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ports,name,' . $port->id,
            'destinations' => 'array',
        ]);

        $port->update(['name' => $request->name]);
        $port->destinations()->sync($request->destinations);

        return redirect()->route('ports.index')->with('success', 'Port mis à jour.');
    }

    public function destroy(Port $port)
    {
        $port->delete();
        return redirect()->route('ports.index')->with('success', 'Port supprimé.');
    }
}
