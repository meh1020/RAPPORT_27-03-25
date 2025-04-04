<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZoneController extends Controller
{
    public function show($id)
    {
        $model = "App\Models\zone_{$id}";
        
        if (class_exists($model)) {
            return view('zone.index', ['articles' => $model::paginate(10), 'id' => $id]);
        }

        abort(404, 'Zone non trouvée');
    }

    private function formatTimeOfFix(?string $timeOfFix): ?string
    {
        if ($timeOfFix) {
            try {
                return Carbon::parse($timeOfFix)->timezone(config('app.timezone'))->toDateTimeString();
            } catch (\Throwable $th) {
                Log::error("Erreur lors du formatage de time_of_fix : " . $th->getMessage());
                return null;
            }
        }
        return null;
    }

    public function importCSV(Request $request, $id)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048' // max 2MB
        ]);

        $file = $request->file('csv_file');

        if (!$file) {
            return back()->with('error', 'Aucun fichier sélectionné.');
        }

        $modelClass = "App\Models\zone_{$id}";

        if (!class_exists($modelClass)) {
            return back()->with('error', "La zone {$id} est invalide.");
        }

        set_time_limit(1200); // Augmente le temps d'exécution pour les fichiers volumineux

        try {
            $handle = fopen($file->getPathname(), 'r');

            if (!$handle) {
                return back()->with('error', "Impossible d'ouvrir le fichier.");
            }

            // Lire la première ligne (en-têtes)
            fgetcsv($handle, 1000, ';');

            $records = [];
            $hashesInCurrentFile = [];
            $totalInserted = 0;
            $totalSkipped = 0;

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                // Vérifier que la ligne possède au moins 14 colonnes
                if (count($data) < 14) {
                    continue;
                }

                $timeOfFix = $this->formatTimeOfFix($data[13]);

                // Calcul d'un hash unique pour la ligne (toutes colonnes)
                $rowHash = md5(implode(';', $data));

                // Vérifier les doublons dans le même fichier
                if (in_array($rowHash, $hashesInCurrentFile)) {
                    $totalSkipped++;
                    continue;
                }
                $hashesInCurrentFile[] = $rowHash;

                $records[] = [
                    'flag'              => $data[0] ?? null,
                    'vessel_name'       => $data[1] ?? null,
                    'registered_owner'  => $data[2] ?? null,
                    'call_sign'         => $data[3] ?? null,
                    'mmsi'              => (int) ($data[4] ?? 0),
                    'imo'               => (int) ($data[5] ?? 0),
                    'ship_type'         => $data[6] ?? null,
                    'destination'       => $data[7] ?? null,
                    'eta'               => $data[8] ?? null,
                    'navigation_status' => $data[9] ?? null,
                    'latitude'          => (float) ($data[10] ?? 0),
                    'longitude'         => (float) ($data[11] ?? 0),
                    'age'               => (int) ($data[12] ?? 0),
                    'time_of_fix'       => $timeOfFix,
                    'row_hash'          => $rowHash,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                // Insertion par lot tous les 1000 enregistrements
                if (count($records) >= 1000) {
                    $this->insertUniqueRecords($records, $modelClass, $totalInserted, $totalSkipped);
                    $records = [];
                }
            }

            fclose($handle);

            // Insertion des enregistrements restants
            if (!empty($records)) {
                $this->insertUniqueRecords($records, $modelClass, $totalInserted, $totalSkipped);
            }

            if ($totalInserted === 0) {
                return back()->with('error', "Aucune nouvelle donnée importée, tous les enregistrements sont des doublons. Doublons ignorés : {$totalSkipped}");
            } else {
                return back()->with('success', "Importation réussie pour la Zone {$id}! Données insérées : {$totalInserted}. Doublons ignorés : {$totalSkipped}");
            }
        } catch (\Exception $e) {
            return back()->with('error', "Erreur lors de l'importation : " . $e->getMessage());
        }
    }

    /**
     * Insère en base uniquement les enregistrements dont le row_hash n'existe pas déjà.
     *
     * @param array  $records      Tableau des enregistrements à insérer
     * @param string $modelClass   Le modèle concerné (ex: App\Models\zone_1)
     * @param int    &$inserted    Compteur des enregistrements insérés
     * @param int    &$skipped     Compteur des doublons ignorés
     */
    private function insertUniqueRecords(array $records, string $modelClass, int &$inserted, int &$skipped)
    {
        // Extraire les valeurs de row_hash du lot
        $hashes = array_column($records, 'row_hash');
        // Récupérer la liste des row_hash déjà présents en base
        $existingHashes = $modelClass::whereIn('row_hash', $hashes)->pluck('row_hash')->toArray();

        // Filtrer pour ne conserver que les enregistrements uniques
        $uniqueRecords = array_filter($records, function ($record) use ($existingHashes) {
            return !in_array($record['row_hash'], $existingHashes);
        });

        $countTotal  = count($records);
        $countUnique = count($uniqueRecords);
        $inserted   += $countUnique;
        $skipped    += ($countTotal - $countUnique);

        if (!empty($uniqueRecords)) {
            $modelClass::insert($uniqueRecords);
        }
    }

    /**
     * Supprime un enregistrement d'une zone.
     *
     * @param int $id       Identifiant de la zone (ex: 1 pour zone_1)
     * @param int $recordId Identifiant de l'enregistrement à supprimer
     */
    public function destroy($id, $recordId)
{
    $modelClass = "App\Models\zone_{$id}";
    if (!class_exists($modelClass)) {
        return back()->with('error', "La zone {$id} est invalide.");
    }

    $record = $modelClass::findOrFail($recordId);
    $record->delete();

    return back()->with('success', "Zone supprimée."); // Utilise 'error' pour une alerte rouge
}

}
