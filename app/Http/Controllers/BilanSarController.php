<?php

namespace App\Http\Controllers;

use App\Models\BilanSar;
use App\Models\Region;
use App\Models\TypeEvenement;
use App\Models\CauseEvenement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BilanSarController extends Controller
{
    public function index(Request $request)
    {
        $query = BilanSar::with(['typeEvenement', 'causeEvenement']);

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

        // Filtrage sur un intervalle de dates : date de début et date de fin
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        
        // Filtre de recherche sur les autres colonnes (sauf 'nom_du_navire')
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('pavillon', 'LIKE', '%' . $search . '%')
                  ->orWhere('immatriculation_callsign', 'LIKE', '%' . $search . '%')
                  ->orWhere('armateur_proprietaire', 'LIKE', '%' . $search . '%')
                  ->orWhere('type_du_navire', 'LIKE', '%' . $search . '%')
                  ->orWhere('coque', 'LIKE', '%' . $search . '%')
                  ->orWhere('propulsion', 'LIKE', '%' . $search . '%')
                  ->orWhere('moyen_d_alerte', 'LIKE', '%' . $search . '%')
                  ->orWhere('description_de_l_evenement', 'LIKE', '%' . $search . '%')
                  ->orWhere('lieu_de_l_evenement', 'LIKE', '%' . $search . '%')
                  ->orWhere('type_d_intervention', 'LIKE', '%' . $search . '%')
                  ->orWhere('description_de_l_intervention', 'LIKE', '%' . $search . '%')
                  ->orWhere('source_de_l_information', 'LIKE', '%' . $search . '%')
                  ->orWhere('pob', 'LIKE', '%' . $search . '%')
                  ->orWhere('survivants', 'LIKE', '%' . $search . '%')
                  ->orWhere('blesses', 'LIKE', '%' . $search . '%')
                  ->orWhere('morts', 'LIKE', '%' . $search . '%')
                  ->orWhere('disparus', 'LIKE', '%' . $search . '%')
                  ->orWhere('evasan', 'LIKE', '%' . $search . '%')
                  ->orWhere('bilan_materiel', 'LIKE', '%' . $search . '%');
            });
        }

        // Tri par date décroissante et pagination (ici 15 par page)
        $bilans = $query->orderBy('date', 'desc')->paginate(15)->appends($request->all());

        return view('surveillance.bilan_sars.index', compact('bilans'));
    }

    public function create()
    {
        $types_evenement = TypeEvenement::all();
        $causes_evenement = CauseEvenement::all();
        $regions = Region::all();
        return view('surveillance.bilan_sars.create', compact('types_evenement', 'causes_evenement', 'regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'nom_du_navire' => 'nullable|string',
            'pavillon' => 'nullable|string',
            'immatriculation_callsign' => 'nullable|string',
            'armateur_proprietaire' => 'nullable|string',
            'type_du_navire' => 'nullable|string',
            'coque' => 'nullable|string',
            'propulsion' => 'nullable|string',
            'moyen_d_alerte' => 'nullable|string',
            'type_d_evenement_id' => 'nullable|exists:type_evenements,id',
            'cause_de_l_evenement_id' => 'nullable|exists:cause_evenements,id',
            'description_de_l_evenement' => 'nullable|string',
            'lieu_de_l_evenement' => 'nullable|string',
            'region_id' => 'nullable|exists:regions,id',
            'type_d_intervention' => 'nullable|string',
            'description_de_l_intervention' => 'nullable|string',
            'source_de_l_information' => 'nullable|string',
            'pob' => 'nullable|integer',
            'survivants' => 'nullable|integer',
            'blesses' => 'nullable|integer',
            'morts' => 'nullable|integer',
            'disparus' => 'nullable|integer',
            'evasan' => 'nullable|integer',
            'bilan_materiel' => 'nullable|string',
        ]);

        BilanSar::create($request->all());

        return redirect()->route('bilan_sars.index')->with('success', 'Bilan SAR ajouté avec succès.');
    }

    public function destroy(BilanSar $bilanSar)
    {
        $bilanSar->delete();
        return redirect()->route('bilan_sars.index');
    }
    
    public function import(Request $request)
    {
        // Validation du fichier CSV
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
    
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
    
        // Ouverture du fichier en lecture
        if (($handle = fopen($path, 'r')) === false) {
            return redirect()->back()->with('error', 'Impossible d\'ouvrir le fichier.');
        }
    
        // Définir l'encodage interne sur UTF-8
        mb_internal_encoding('UTF-8');
    
        // Définir le délimiteur utilisé dans votre CSV
        $delimiter = ';';
    
        // Lecture de la première ligne (les en-têtes)
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            return redirect()->back()->with('error', 'Fichier CSV vide ou invalide.');
        }
    
        // Supprimer le BOM s'il existe et nettoyer les en-têtes
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);
    
        // Traitement de chaque ligne du fichier
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Vérifier le nombre de colonnes
            if (count($row) < count($header)) {
                continue;
            }
            if (count($row) > count($header)) {
                $row = array_slice($row, 0, count($header));
            }
    
            // Conversion de chaque champ en UTF-8 depuis Windows-1252
            $row = array_map(function($value) {
                return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }, $row);
    
            // Création d'un tableau associatif à partir des en-têtes et de la ligne
            $rowData = array_combine($header, $row);
    
            // Conversion de la date du format dd/mm/yyyy au format yyyy-mm-dd
            $dateRaw = $rowData['DATE'] ?? null;
            $date = null;
            if ($dateRaw) {
                try {
                    $date = Carbon::createFromFormat('d/m/Y', trim($dateRaw))->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }
    
            // Extraction des autres valeurs depuis le CSV
            $nom_du_navire                = $rowData['NOM DU NAVIRE'] ?? null;
            $pavillon                     = $rowData['PAVILLON'] ?? null;
            $immatriculation_callsign     = $rowData['IMMATRICULATION/CALLSIGN'] ?? null;
            $armateur_proprietaire        = $rowData['ARMATEUR/PROPRIETAIRE'] ?? null;
            $type_du_navire               = $rowData['TYPE DU NAVIRE'] ?? null;
            $coque                        = $rowData['COQUE'] ?? null;
            $propulsion                   = $rowData['PROPULSION'] ?? null;
            $moyen_d_alerte               = $rowData['MOYEN D\'ALERTE'] ?? null;
            $typeEvenementName            = $rowData["TYPE D'EVENEMENT"] ?? null;
            $causeEvenementName           = $rowData["CAUSE DE L'EVENEMENT"] ?? null;
            $description_de_l_evenement   = $rowData["DESCRIPTION DE L'EVENEMENT"] ?? null;
            $lieu_de_l_evenement          = $rowData["LIEU DE L'EVENEMENT"] ?? null;
            $regionName                   = $rowData["REGION"] ?? null;
            $type_d_intervention          = $rowData["TYPE D'INTERVENTION"] ?? null;
            $description_de_l_intervention= $rowData["DESCRIPTION DE L'INTERVENTION"] ?? null;
            $source_de_l_information      = $rowData["SOURCE DE L'INFORMATION"] ?? null;
    
            // Traitement des colonnes numériques
            $pob = trim($rowData["POB"] ?? '');
            $pob = ($pob === '_' || $pob === '' || $pob === '-') ? null : $pob;
            
            $survivants = trim($rowData["SURVIVANTS"] ?? '');
            $survivants = ($survivants === '_' || $survivants === '' || $survivants === '-') ? null : $survivants;
            
            $blesses = trim($rowData["BLESSES"] ?? '');
            $blesses = ($blesses === '_' || $blesses === '' || $blesses === '-') ? null : $blesses;
            
            $morts = trim($rowData["MORTS"] ?? '');
            $morts = ($morts === '_' || $morts === '' || $morts === '-') ? null : $morts;
            
            $disparus = trim($rowData["DISPARUS"] ?? '');
            $disparus = ($disparus === '_' || $disparus === '' || $disparus === '-') ? null : $disparus;
    
            $evasan = trim($rowData["EVASAN"] ?? '');
            $evasan = ($evasan === '_' || $evasan === '' || $evasan === '-') ? null : $evasan;
    
            $bilan_materiel = trim($rowData["BILAN MATERIEL"] ?? '');
            $bilan_materiel = ($bilan_materiel === '_' || $bilan_materiel === '' || $bilan_materiel === '-') ? null : $bilan_materiel;
    
            // Récupération (ou création) du Type d'Événement
            $typeEvenement = null;
            if ($typeEvenementName) {
                $typeEvenement = TypeEvenement::firstOrCreate(['nom' => trim($typeEvenementName)]);
            }
    
            // Récupération (ou création) de la Cause d'Événement
            $causeEvenement = null;
            if ($causeEvenementName) {
                $causeEvenement = CauseEvenement::firstOrCreate(['nom' => trim($causeEvenementName)]);
            }
    
            // Récupération (ou création) de la Région
            $region = null;
            if ($regionName) {
                $region = Region::firstOrCreate(['nom' => trim($regionName)]);
            }
            
            // Création du Bilan SAR avec les données extraites
            BilanSar::create([
                'date'                          => $date,
                'nom_du_navire'                 => $nom_du_navire,
                'pavillon'                      => $pavillon,
                'immatriculation_callsign'      => $immatriculation_callsign,
                'armateur_proprietaire'         => $armateur_proprietaire,
                'type_du_navire'                => $type_du_navire,
                'coque'                         => $coque,
                'propulsion'                    => $propulsion,
                'moyen_d_alerte'                => $moyen_d_alerte,
                'type_d_evenement_id'           => $typeEvenement ? $typeEvenement->id : null,
                'cause_de_l_evenement_id'       => $causeEvenement ? $causeEvenement->id : null,
                'description_de_l_evenement'    => $description_de_l_evenement,
                'lieu_de_l_evenement'           => $lieu_de_l_evenement,
                'region_id'                     => $region ? $region->id : null,
                'type_d_intervention'           => $type_d_intervention,
                'description_de_l_intervention' => $description_de_l_intervention,
                'source_de_l_information'       => $source_de_l_information,
                'pob'                           => $pob,
                'survivants'                    => $survivants,
                'blesses'                       => $blesses,
                'morts'                         => $morts,
                'disparus'                      => $disparus,
                'evasan'                        => $evasan,
                'bilan_materiel'                => $bilan_materiel,
            ]);
        }
    
        fclose($handle);
    
        return redirect()->route('bilan_sars.index')->with('success', 'Fichier CSV importé avec succès.');
    }
}
