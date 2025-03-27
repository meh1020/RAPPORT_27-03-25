<?php

namespace App\Http\Controllers;

use App\Models\PassageInoffensif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PassageInoffensifController extends Controller
{
    // Affiche la liste de tous les passages inoffensifs
    public function index(Request $request)
    {
        $query = PassageInoffensif::query();

        // Filtrage par année sur la date d'entrée
        if ($request->filled('year')) {
            $query->whereYear('date_entree', $request->year);
        }

        // Filtrage par mois sur la date d'entrée
        if ($request->filled('month')) {
            $query->whereMonth('date_entree', $request->month);
        }

        // Filtrage par jour sur la date d'entrée
        if ($request->filled('day')) {
            $query->whereDay('date_entree', $request->day);
        }

        // Filtrage sur un intervalle de dates (date de début et date de fin) sur la date d'entrée
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date_entree', [$request->start_date, $request->end_date]);
        }

        // Recherche full text sur le champ 'navire'
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('navire', 'LIKE', '%' . $search . '%');
        }

        // Récupération des résultats paginés, triés par date d'entrée décroissante et conservation des paramètres
        $passages = $query->orderBy('date_entree', 'desc')->paginate(50)->appends($request->all());

        return view('passage_inoffensif.index', compact('passages'));
    }

    // Affiche le formulaire de création d'un nouveau passage inoffensif
    public function create()
    {
        return view('passage_inoffensif.create');
    }

    // Stocke un nouveau passage inoffensif dans la base de données
    public function store(Request $request)
    {
        $request->validate([
            'date_entree' => 'required|date',
            'date_sortie' => 'required|date|after_or_equal:date_entree',
            'navire'      => 'required|string|max:255',
        ]);

        PassageInoffensif::create($request->all());

        return redirect()->route('passage_inoffensifs.index')
                         ->with('success', 'Passage inoffensif créé avec succès.');
    }

    // Supprime un passage inoffensif
    public function destroy($id)
    {
        $passage = PassageInoffensif::findOrFail($id);
        $passage->delete();

        return redirect()->route('passage_inoffensifs.index')
                         ->with('success', 'Passage inoffensif supprimé avec succès.');
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), "r");

        if ($handle !== false) {
            $rowNumber = 0;

            // Lecture et validation de la première ligne (en-têtes) avec le bon délimiteur
            $headers = fgetcsv($handle, 1000, ";");
            if (!$headers || count($headers) < 3) {
                fclose($handle);
                return redirect()->back()->withErrors(['csv_file' => 'Le fichier CSV ne contient pas de colonnes valides.']);
            }

            // Boucle sur les lignes suivantes
            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                $rowNumber++;

                // Vérification du nombre de colonnes attendu
                if (count($row) < 3) {
                    \Log::error("Ligne $rowNumber ignorée : Données incomplètes.");
                    continue;
                }

                // Mapping des colonnes
                $navire     = trim($row[0]);
                $dateEntree = trim($row[1]);
                $dateSortie = trim($row[2]);

                // Conversion des dates au format d/m/Y en objet DateTime
                $dateEntreeObj = \DateTime::createFromFormat('d/m/Y', $dateEntree);
                $dateSortieObj = \DateTime::createFromFormat('d/m/Y', $dateSortie);

                if (!$dateEntreeObj || !$dateSortieObj) {
                    \Log::error("Ligne $rowNumber ignorée : Format de date incorrect (date_entree : $dateEntree, date_sortie : $dateSortie).");
                    continue;
                }

                // Formatage des dates au format YYYY-MM-DD
                $dateEntree = $dateEntreeObj->format('Y-m-d');
                $dateSortie = $dateSortieObj->format('Y-m-d');

                // Conversion de l'encodage pour le nom du navire
                $encoding = mb_detect_encoding($navire, 'UTF-8, ISO-8859-1, WINDOWS-1252', true);
                if ($encoding !== 'UTF-8') {
                    $navire = mb_convert_encoding($navire, 'UTF-8', $encoding);
                }
                $navire = preg_replace('/^\xEF\xBB\xBF/', '', $navire);

                \Log::info("Insertion de la ligne $rowNumber : $dateEntree | $dateSortie | $navire");

                PassageInoffensif::create([
                    'date_entree' => $dateEntree,
                    'date_sortie' => $dateSortie,
                    'navire'      => $navire,
                ]);
            }
            fclose($handle);
        }

        return redirect()->back()->with('success', 'Données importées avec succès !');
    }
}
