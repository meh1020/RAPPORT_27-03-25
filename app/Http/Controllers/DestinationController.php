<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;

class DestinationController extends Controller
{
    /**
     * Affiche la liste de toutes les destinations.
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
    
        if ($query) {
            // Recherche partielle sur le champ 'name'
            $destinations = Destination::where('name', 'LIKE', '%' . $query . '%')->paginate(10);
        } else {
            $destinations = Destination::paginate(10);
        }
    
        return view('destinationmada.index', compact('destinations'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle destination.
     */
    public function create()
    {
        return view('destinationmada.create');
    }

    /**
     * Stocke une nouvelle destination dans la base de données.
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                // On effectue une comparaison sensible à la casse grâce à l'opérateur BINARY.
                if (Destination::whereRaw('BINARY name = ?', [$value])->exists()) {
                    $fail('La destination existe déjà avec cette écriture exacte.');
                }
            },
        ],
    ]);

    Destination::create([
        'name' => $request->input('name'),
    ]);

    return redirect()->route('destinations.index')->with('success', 'Destination ajoutée avec succès.');
}


    public function destroy(Destination $destination)
    {
        $destination->delete();
        return redirect()->route('destinations.index')
                         ->with('success', 'Destination supprimée avec succès.');
    }

    public function importStore(Request $request)
{
    // Validation du fichier uploadé
    $request->validate([
        'file' => 'required|file|mimes:txt'
    ]);

    // Récupérer le contenu du fichier
    $fileContent = file_get_contents($request->file('file')->getRealPath());

    // Extraction de toutes les chaînes entre guillemets doubles directement sur tout le contenu
    preg_match_all('/"([^"]+)"/', $fileContent, $matches);
    if (empty($matches[1])) {
        return redirect()->back()->with('error', 'Aucune destination trouvée dans le fichier.');
    }
    $destinationsArray = $matches[1];

    $imported = 0;
    // Boucle sur chaque destination extraite et insertion si elle n'existe pas déjà (comparaison sensible à la casse)
    foreach ($destinationsArray as $destinationName) {
        if (!Destination::whereRaw('BINARY name = ?', [$destinationName])->exists()) {
            Destination::create(['name' => $destinationName]);
            $imported++;
        }
    }

    return redirect()->route('destinations.index')->with('success', "$imported destinations importées avec succès.");
}

}
