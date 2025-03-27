<?php

namespace App\Http\Controllers;

use App\Models\Mer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MerTerritorial extends Controller
{
    public function index()
    {
        $mers = Mer::paginate(20);
        return view('mer_territorial.index', compact('mers'));
    }

    public function destroy(Mer $mers)
    {
        $mers->delete();
        return redirect()->route('mer_territorial.index')->with('success', 'mers territorial supprimé.');
    }


    public function importCSV(Request $request)
{
    $file = $request->file('csv_file');
    set_time_limit(1200);

    // Initialisation des compteurs
    $totalInserted = 0;
    $totalSkipped  = 0;

    if ($file) {
        $handle = fopen($file->getPathname(), 'r');
        // Ignorer la ligne d'en-tête
        fgetcsv($handle, 1000, ';');

        $rowsToInsert = [];
        $hashesInCurrentFile = []; // Pour détecter les doublons internes au fichier

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            if (count($data) < 14) {
                continue; // Ignorer les lignes incomplètes
            }

            // Calculer un hash unique basé sur la ligne entière
            $rowHash = md5(implode(';', $data));

            // Vérifier si ce hash a déjà été rencontré dans le même fichier
            if (in_array($rowHash, $hashesInCurrentFile)) {
                $totalSkipped++; // Ce doublon est ignoré
                continue;
            }
            $hashesInCurrentFile[] = $rowHash;

            $timeOfFix = $this->formatTimeOfFix($data[13]);

            $rowsToInsert[] = [
                'flag'              => $data[0],
                'vessel_name'       => $data[1],
                'registered_owner'  => $data[2],
                'call_sign'         => $data[3],
                'mmsi'              => $data[4],
                'imo'               => $data[5],
                'ship_type'         => $data[6],
                'destination'       => $data[7],
                'eta'               => $data[8],
                'navigation_status' => $data[9],
                'latitude'          => $data[10],
                'longitude'         => $data[11],
                'age'               => $data[12],
                'time_of_fix'       => $timeOfFix,
                'row_hash'          => $rowHash,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Insertion par lots pour améliorer la performance
            if (count($rowsToInsert) >= 1000) {
                $this->insertUniqueRows($rowsToInsert, $totalInserted, $totalSkipped);
                $rowsToInsert = [];
            }
        }

        // Insertion du reste des lignes si le nombre est inférieur à 1000
        if (!empty($rowsToInsert)) {
            $this->insertUniqueRows($rowsToInsert, $totalInserted, $totalSkipped);
        }

        fclose($handle);
    }

    // Si aucune nouvelle donnée n'a été insérée, renvoyer un message d'erreur
    if ($totalInserted === 0) {
        return back()->with('error', 'Aucune nouvelle donnée importée, tous les enregistrements sont des doublons.');
    } else {
        return back()->with('success', "Importation terminée. {$totalInserted} nouvelles lignes ont été ajoutées. {$totalSkipped} doublons ont été ignorés.");
    }
}

/**
 * Insère uniquement les lignes dont le 'row_hash' n'existe pas déjà en base, 
 * et met à jour les compteurs d'insertion et de doublons.
 *
 * @param array $rows
 * @param int   &$inserted  (compteur de lignes insérées)
 * @param int   &$skipped   (compteur de doublons ignorés)
 */
private function insertUniqueRows(array $rows, int &$inserted, int &$skipped)
{
    // Récupérer les row_hash du lot
    $hashes = array_column($rows, 'row_hash');
    // Vérifier dans la base ceux déjà existants
    $existingHashes = Mer::whereIn('row_hash', $hashes)->pluck('row_hash')->toArray();

    // Filtrer uniquement les lignes qui ne sont pas déjà en base
    $uniqueRows = array_filter($rows, function($row) use ($existingHashes) {
         return !in_array($row['row_hash'], $existingHashes);
    });

    $countRows  = count($rows);
    $countUnique = count($uniqueRows);
    $inserted   += $countUnique;
    $skipped    += ($countRows - $countUnique);

    if (!empty($uniqueRows)) {
       Mer::insert($uniqueRows);
    }
}


    private function formatTimeOfFix(?string $timeOfFix): ?string
    {
        if ($timeOfFix) {
            try {
                if (strpos($timeOfFix, 'Z') !== false) {
                    return Carbon::parse($timeOfFix)->timezone(config('app.timezone'))->toDateTimeString();
                } else {
                    return Carbon::parse($timeOfFix)->timezone(config('app.timezone'))->toDateTimeString();
                }
            } catch (\Throwable $th) {
                Log::error("Erreur lors du formatage de time_of_fix : " . $th->getMessage());
                return null;
            }
        }

        return null;
    }
}
