<?php

namespace App\Http\Controllers;

use App\Models\Peche;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PecheController extends Controller
{
    public function index()
    {
        $peches = Peche::paginate(20);
        return view('peche.index', compact('peches'));
    }

    public function destroy(Peche $peche)
    {
        $peche->delete();
        return redirect()->route('peche.index')->with('success', 'Pêche supprimée.');
    }

    public function importCSV(Request $request)
    {
        $file = $request->file('csv_file');
        set_time_limit(1200);

        // Initialiser les compteurs
        $totalInserted = 0;
        $totalSkipped  = 0;

        if ($file) {
            $handle = fopen($file->getPathname(), 'r');
            // Ignorer la ligne d'en-tête
            fgetcsv($handle, 1000, ';');

            $rowsToInsert = [];
            $hashesInCurrentFile = []; // Pour détecter les doublons dans un même fichier

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                if (count($data) < 14) {
                    continue; // Ignorer les lignes incomplètes
                }

                // Calculer un hash unique basé sur la ligne entière
                $rowHash = md5(implode(';', $data));

                // Si le hash existe déjà dans le fichier, on incrémente le compteur et on passe à la suivante
                if (in_array($rowHash, $hashesInCurrentFile)) {
                    $totalSkipped++;
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

                // Insertion par lot pour améliorer les performances
                if (count($rowsToInsert) >= 1000) {
                    $this->insertUniqueRows($rowsToInsert, $totalInserted, $totalSkipped);
                    $rowsToInsert = [];
                }
            }

            if (!empty($rowsToInsert)) {
                $this->insertUniqueRows($rowsToInsert, $totalInserted, $totalSkipped);
            }

            fclose($handle);
        }

        // Si aucune nouvelle donnée n'a été insérée, retourner un message d'erreur
        if ($totalInserted === 0) {
            return back()->with('error', 'Aucune nouvelle donnée importée, tous les enregistrements sont des doublons.');
        } else {
            return back()->with('success', "Importation terminée. {$totalInserted} nouvelles lignes ont été ajoutées. {$totalSkipped} doublons ont été ignorés.");
        }
    }

    private function formatTimeOfFix(?string $timeOfFix): ?string
    {
        if ($timeOfFix) {
            try {
                // Exemple de formatage (vous pouvez l'adapter selon vos besoins)
                return Carbon::parse($timeOfFix)->timezone(config('app.timezone'))->toDateTimeString();
            } catch (\Throwable $th) {
                Log::error("Erreur lors du formatage de time_of_fix : " . $th->getMessage());
                return null;
            }
        }
        return null;
    }

    /**
     * Insère uniquement les lignes dont le row_hash n'existe pas déjà en base, 
     * et met à jour les compteurs d'insertion et de doublons.
     *
     * @param array $rows
     * @param int   &$inserted  (compteur de lignes insérées)
     * @param int   &$skipped   (compteur de doublons ignorés)
     */
    private function insertUniqueRows(array $rows, int &$inserted, int &$skipped)
    {
        // Extraire les row_hash du lot
        $hashes = array_column($rows, 'row_hash');
        // Récupérer de la base les row_hash déjà existantes
        $existingHashes = Peche::whereIn('row_hash', $hashes)->pluck('row_hash')->toArray();

        // Conserver uniquement les lignes uniques
        $uniqueRows = array_filter($rows, function ($row) use ($existingHashes) {
            return !in_array($row['row_hash'], $existingHashes);
        });

        $countRows   = count($rows);
        $countUnique = count($uniqueRows);
        $inserted   += $countUnique;
        $skipped    += ($countRows - $countUnique);

        if (!empty($uniqueRows)) {
            Peche::insert($uniqueRows);
        }
    }
}
