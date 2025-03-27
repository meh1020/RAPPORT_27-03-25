<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabotage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CabotageController extends Controller
{
     // Affiche la liste de tous les enregistrements
     public function index(Request $request)
    {
        // Initialisation de la requête sur le modèle Cabotage
        $query = Cabotage::query();

        // Filtrage par année
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Filtrage par mois
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        // Filtrage par jour
        if ($request->filled('day')) {
            $query->whereDay('date', $request->day);
        }

        // Filtrage par intervalle de dates (date de début et date de fin)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        
        // Recherche full text sur certains champs (ici 'provenance' et 'navires')
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('provenance', 'LIKE', '%'.$search.'%')
                ->orWhere('navires', 'LIKE', '%'.$search.'%');
            });
        }

        // Récupération des résultats paginés (50 par page) avec conservation des paramètres de la requête
        $cabotages = $query->orderBy('date', 'desc')->paginate(50)->appends($request->all());

        return view('surveillance.cabotage.index', compact('cabotages'));
    }

     // Affiche le formulaire de création
     public function create()
     {
         return view('surveillance.cabotage.create');
     }
 
     // Enregistre un nouveau enregistrement dans la base de données
     public function store(Request $request)
     {
         // Valider les données du formulaire
         $validated = $request->validate([
             'date' => 'required|date',
             'provenance' => 'required|string|max:255',
             'navires' => 'required|string|max:255',
             'equipage' => 'required|integer',
             'passagers' => 'required|integer',
         ]);
 
         // Créer le nouvel enregistrement
         Cabotage::create($validated);
 
         // Rediriger vers la liste avec un message de succès
         return redirect()->route('cabotage.index')->with('success', 'Enregistrement effectué avec succès !');
     }

     // Supprime un enregistrement spécifique
    public function destroy($id)
    {
        $cabotage = Cabotage::find($id);

        if (!$cabotage) {
            return redirect()->route('cabotage.index')->with('error', 'Enregistrement non trouvé.');
        }

        $cabotage->delete();

        return redirect()->route('cabotage.index')->with('success', 'Enregistrement supprimé avec succès.');
    }

    public function import(Request $request)
    {
        // 1) Validation du fichier CSV
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:8192', // 8192 kilooctets = 8 Mo
        ]);
    
        // 2) Récupération du fichier et vérification
        $file = $request->file('csv_file');
    
        // Vérification de la validité du fichier uploadé
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Erreur lors de l\'upload : ' . $file->getErrorMessage());
        }
    
        $path = $file->getRealPath();
    
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'Impossible de trouver le fichier.');
        }
    
        // Pour éviter un timeout si le fichier est très gros
        set_time_limit(0);
    
        // 3) Ouverture du fichier
        $handle = fopen($path, 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Impossible d\'ouvrir le fichier.');
        }
    
        // Lecture de la première ligne (en-tête)
        fgetcsv($handle, 0, ';');
    
        // Préparez une variable pour faire des insertions en lot
        $batchData = [];
        $batchSize = 1000; // Nombre de lignes à accumuler avant insertion
        $rowCount  = 0;
    
        // 4) Lecture ligne par ligne
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Vérifier que la ligne contient au moins 5 colonnes
            if (count($row) < 5) {
                continue; // Ignorez les lignes incomplètes
            }
            $row = array_map(function($value) {
                return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }, $row);
    
            // Récupération des valeurs
            $rawDate     = $row[0] ?? null;
            $provenance  = $row[1] ?? null;
            $navires     = $row[2] ?? null;
            $equipage    = $row[3] ?? null;
            $passagers   = $row[4] ?? null;
    
            // Conversion de la date de dd/mm/yyyy à yyyy-mm-dd
            $date = null;
            if (!empty($rawDate)) {
                try {
                    $date = Carbon::createFromFormat('d/m/Y', trim($rawDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    // En cas d'erreur de conversion, on peut choisir d'ignorer la ligne
                    continue;
                }
            }
    
            // Ajout dans le batch
            $batchData[] = [
                'date'       => $date,
                'provenance' => $provenance,
                'navires'    => $navires,
                'equipage'   => (int) $equipage,
                'passagers'  => (int) $passagers,
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $rowCount++;
    
            // Insertion en lot lorsque le batch est complet
            if ($rowCount % $batchSize === 0) {
                DB::table('cabotages')->insertOrIgnore($batchData);
                $batchData = []; // Réinitialisation du batch
            }
        }
    
        // 5) Insertion des dernières lignes s'il en reste
        if (count($batchData) > 0) {
            DB::table('cabotages')->insertOrIgnore($batchData);
        }
    
        fclose($handle);
    
        return redirect()->route('cabotage.index')
                         ->with('success', 'Importation terminée !');
    }
    
    
}
