<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Avurnav;
use App\Models\Pollution;
use App\Models\Sitrep;
use App\Models\BilanSar;
use App\Models\Region;
use App\Models\Peche;
use App\Models\Vedette;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf; // Alias pour Barryvdh\DomPDF\Facade

class RapportController extends Controller
{
    /**
     * Affiche le rapport avec les filtres appliqués (version HTML).
     */
    public function index(Request $request)
    {
        // Comptages globaux (non filtrés, à adapter si nécessaire)
        $articleCount   = Article::count();
        $avurnavCount   = Avurnav::count();
        $pollutionCount = Pollution::count();
        $sitrepCount    = Sitrep::count();
        $bilanSarCount  = BilanSar::count();
    
        // Récupération des paramètres de filtre
        $dateFilter      = $request->input('filter_date');         // Format : YYYY-MM-DD
        $yearQuarter     = $request->input('filter_year_quarter');   // Année pour le trimestre
        $quarter         = $request->input('filter_quarter');        // 1, 2, 3 ou 4
        $yearMonth       = $request->input('filter_year_month');     // Année pour le mois
        $month           = $request->input('filter_month');          // Mois (1 à 12)
        $filterYear      = $request->input('filter_year');           // Année (filtre annuel)
    
        // Préparation des dates de début et de fin pour le filtre par trimestre
        if ($yearQuarter && $quarter) {
            switch ($quarter) {
                case 1:
                    $start = "$yearQuarter-01-01";
                    $end   = "$yearQuarter-03-31";
                    break;
                case 2:
                    $start = "$yearQuarter-04-01";
                    $end   = "$yearQuarter-06-30";
                    break;
                case 3:
                    $start = "$yearQuarter-07-01";
                    $end   = "$yearQuarter-09-30";
                    break;
                case 4:
                    $start = "$yearQuarter-10-01";
                    $end   = "$yearQuarter-12-31";
                    break;
                default:
                    $start = null;
                    $end   = null;
                    break;
            }
        }
    
        /*
         * Pour BilanSar :
         * - Si le champ "date" est renseigné, on filtre sur ce champ.
         * - Sinon, on filtre sur "created_at" via COALESCE(`date`, created_at).
         */
        $bilanSarQuery = BilanSar::query();
        if ($dateFilter) {
            $bilanSarQuery->whereRaw("DATE(COALESCE(`date`, created_at)) = ?", [$dateFilter]);
        } elseif ($yearQuarter && $quarter && isset($start, $end)) {
            $bilanSarQuery->whereRaw("DATE(COALESCE(`date`, created_at)) BETWEEN ? AND ?", [$start, $end]);
        } elseif ($yearMonth && $month) {
            $bilanSarQuery->whereRaw("YEAR(COALESCE(`date`, created_at)) = ? AND MONTH(COALESCE(`date`, created_at)) = ?", [$yearMonth, $month]);
        } elseif ($filterYear) {
            $bilanSarQuery->whereRaw("YEAR(COALESCE(`date`, created_at)) = ?", [$filterYear]);
        }
    
        // Types d'événements
        $typesData = (clone $bilanSarQuery)
            ->selectRaw('type_d_evenement_id, COUNT(*) as count')
            ->groupBy('type_d_evenement_id')
            ->with('typeEvenement')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->typeEvenement->nom ?? 'Inconnu',
                    'count' => $item->count,
                ];
            });
    
        // Causes d'événements
        $causesData = (clone $bilanSarQuery)
            ->selectRaw('cause_de_l_evenement_id, COUNT(*) as count')
            ->groupBy('cause_de_l_evenement_id')
            ->with('causeEvenement')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->causeEvenement->nom ?? 'Inconnu',
                    'count' => $item->count,
                ];
            });
    
        // Bilans SAR par région
        $regionsData = (clone $bilanSarQuery)
            ->selectRaw('region_id, COUNT(*) as count')
            ->groupBy('region_id')
            ->with('region')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->region->nom ?? 'Inconnu',
                    'count' => $item->count,
                ];
            });
    
        // Statistiques SAR
        $bilanStats = (clone $bilanSarQuery)
            ->selectRaw('
                SUM(pob) as pob_total, 
                SUM(survivants) as survivants_total, 
                SUM(blesses) as blesses_total, 
                SUM(morts) as morts_total, 
                SUM(disparus) as disparus_total, 
                SUM(evasan) as evasan_total
            ')
            ->first();
    
        /*
         * Pour Peche et les zones, on applique le filtre sur "time_of_fix" (format ISO).
         */
        // Zones
        $zoneCounts = [];
        for ($i = 1; $i <= 9; $i++) {
            $modelClass = "App\\Models\\zone_$i";
            if (class_exists($modelClass)) {
                $query = $modelClass::query();
                if ($dateFilter) {
                    $query->whereDate('time_of_fix', $dateFilter);
                } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                    $query->whereBetween('time_of_fix', [$start, $end]);
                } elseif ($yearMonth && $month) {
                    $query->whereYear('time_of_fix', $yearMonth)
                          ->whereMonth('time_of_fix', $month);
                } elseif ($filterYear) {
                    $query->whereYear('time_of_fix', $filterYear);
                }
                $zoneCounts["Zone $i"] = $query->count();
            }
        }
    
        // Flags (navires de pêche)
        $flagQuery = Peche::query();
        if ($dateFilter) {
            $flagQuery->whereDate('time_of_fix', $dateFilter);
        } elseif ($yearQuarter && $quarter && isset($start, $end)) {
            $flagQuery->whereBetween('time_of_fix', [$start, $end]);
        } elseif ($yearMonth && $month) {
            $flagQuery->whereYear('time_of_fix', $yearMonth)
                      ->whereMonth('time_of_fix', $month);
        } elseif ($filterYear) {
            $flagQuery->whereYear('time_of_fix', $filterYear);
        }
        $flagData = $flagQuery
            ->selectRaw('flag, COUNT(*) as count')
            ->groupBy('flag')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->flag,
                    'count' => $item->count,
                ];
            });
    
        // Articles (types de navires)
        $shipTypesQuery = Article::query();
        if ($dateFilter) {
            $shipTypesQuery->whereDate('time_of_fix', $dateFilter);
        } elseif ($yearQuarter && $quarter && isset($start, $end)) {
            $shipTypesQuery->whereBetween('time_of_fix', [$start, $end]);
        } elseif ($yearMonth && $month) {
            $shipTypesQuery->whereYear('time_of_fix', $yearMonth)
                            ->whereMonth('time_of_fix', $month);
        } elseif ($filterYear) {
            $shipTypesQuery->whereYear('time_of_fix', $filterYear);
        }
            
        $shipTypesData = $shipTypesQuery
            ->selectRaw('ship_type, COUNT(*) as count')
            ->groupBy('ship_type')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->ship_type,
                    'count' => $item->count,
                ];
            });
    
        //  AJOUT : Filtrage Cabotage
        // --------------------------------------------
        $cabotageQuery = \App\Models\Cabotage::query();
        if ($dateFilter) {
            $cabotageQuery->whereDate('date', $dateFilter);
        } elseif (isset($start, $end)) {
            $cabotageQuery->whereBetween('date', [$start, $end]);
        } elseif ($yearMonth && $month) {
            $cabotageQuery->whereYear('date', $yearMonth)
                          ->whereMonth('date', $month);
        } elseif ($filterYear) {
            $cabotageQuery->whereYear('date', $filterYear);
        }
    
        //  AJOUT : Filtrage Vedette sar
        // --------------------------------------------
        $VedetteQuery = \App\Models\Vedette::query();
        if ($dateFilter) {
            $VedetteQuery->whereDate('date', $dateFilter);
        } elseif (isset($start, $end)) {
            $VedetteQuery->whereBetween('date', [$start, $end]);
        } elseif ($yearMonth && $month) {
            $VedetteQuery->whereYear('date', $yearMonth)
                         ->whereMonth('date', $month);
        } elseif ($filterYear) {
            $VedetteQuery->whereYear('date', $filterYear);
        }
    
        // Pour chaque provenance, on compte le nombre de navires distincts et on somme équipage & passagers
        $cabotageData = $cabotageQuery
            ->selectRaw('
                provenance,
                COUNT(DISTINCT navires) as total_navires,
                SUM(equipage) as total_equipage,
                SUM(passagers) as total_passagers
            ')
            ->groupBy('provenance')
            ->get();
    
        // Construction du texte récapitulatif du filtre
        if ($dateFilter) {
            $filterResult = "Données du " . $dateFilter;
        } elseif ($yearQuarter && $quarter) {
            $qText = ($quarter == 1) ? "1er trimestre" : $quarter . "ème trimestre";
            $filterResult = "Données de l'année $yearQuarter - $qText";
        } elseif ($yearMonth && $month) {
            $months = [
                1 => "janvier", 2 => "février", 3 => "mars", 4 => "avril",
                5 => "mai", 6 => "juin", 7 => "juillet", 8 => "août",
                9 => "septembre", 10 => "octobre", 11 => "novembre", 12 => "décembre"
            ];
            $monthName = $months[(int)$month] ?? $month;
            $filterResult = "Données de l'année $yearMonth - mois de $monthName";
        } elseif ($filterYear) {
            $filterResult = "Données de l'année $filterYear";
        } else {
            $filterResult = "Toutes les données";
        }
    
        // Calcul du graphique "Articles par Port"
        $articlesForGraph = Article::query();
        if ($dateFilter) {
            $articlesForGraph->whereDate('time_of_fix', $dateFilter);
        } elseif ($yearQuarter && $quarter && isset($start, $end)) {
            $articlesForGraph->whereBetween('time_of_fix', [$start, $end]);
        } elseif ($yearMonth && $month) {
            $articlesForGraph->whereYear('time_of_fix', $yearMonth)
                            ->whereMonth('time_of_fix', $month);
        } elseif ($filterYear) {
            $articlesForGraph->whereYear('time_of_fix', $filterYear);
        }
    
        // Appliquer le filtre sur la colonne "destination" en se basant sur les noms dans le modèle Destination
        $filtreDestinations = \App\Models\Destination::pluck('name')->toArray();
        $articlesForGraph->where(function ($q) use ($filtreDestinations) {
            foreach ($filtreDestinations as $destination) {
                $q->orWhere('destination', $destination);
            }
            // Inclure les destinations commençant par "Mg"
            $q->orWhere('destination', 'LIKE', 'Mg%');
        })
        ->where('ship_type', '!=', 'Tug')
        ->whereNotIn('vessel_name', ['TSARAVATSY', 'AVISOA', 'TS INDIAN OCEAN'])
        ->where('flag', '!=', 'Madagascar');
    
        $articlesForGraph = $articlesForGraph->get();
    
        // Construction du tableau de comptage par port
        $portCounts = [];
        foreach ($articlesForGraph as $article) {
            // Recherche de la destination correspondante dans le modèle Destination
            $destination = \App\Models\Destination::where('name', $article->destination)->first();
            if ($destination) {
                // Pour chaque port associé à la destination, on incrémente le compteur
                foreach ($destination->ports as $port) {
                    $portName = $port->name;
                    if (isset($portCounts[$portName])) {
                        $portCounts[$portName]++;
                    } else {
                        $portCounts[$portName] = 1;
                    }
                }
            }
        }
    
        // Retourne la vue HTML
        return view('rapport', compact(
            'articleCount',
            'avurnavCount',
            'pollutionCount',
            'sitrepCount',
            'bilanSarCount',
            'typesData',
            'causesData',
            'regionsData',
            'shipTypesData',
            'bilanStats',
            'zoneCounts',
            'flagData',
            'filterResult',
            'cabotageData',
            'portCounts'
        ));
    }    

    /**
     * Exporte en PDF les mêmes données filtrées.
     */
    public function exportPdf(Request $request)
    {
        // Augmenter le temps d'exécution et la mémoire si nécessaire
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        // Récupération des filtres
        $dateFilter   = $request->input('filter_date');
        $yearQuarter  = $request->input('filter_year_quarter');
        $quarter      = $request->input('filter_quarter');
        $yearMonth    = $request->input('filter_year_month');
        $month        = $request->input('filter_month');
        $filterYear   = $request->input('filter_year'); // Ajout du filtre annuel

        // Détermination de la plage de dates pour le trimestre
        $start = null;
        $end   = null;
        if ($yearQuarter && $quarter) {
            switch ($quarter) {
                case 1:
                    $start = "$yearQuarter-01-01";
                    $end   = "$yearQuarter-03-31";
                    break;
                case 2:
                    $start = "$yearQuarter-04-01";
                    $end   = "$yearQuarter-06-30";
                    break;
                case 3:
                    $start = "$yearQuarter-07-01";
                    $end   = "$yearQuarter-09-30";
                    break;
                case 4:
                    $start = "$yearQuarter-10-01";
                    $end   = "$yearQuarter-12-31";
                    break;
                default:
                    $start = null;
                    $end   = null;
            }
        }

        // Construction de la requête filtrée pour BilanSar
        $bilanSarQuery = BilanSar::query();
        if ($dateFilter) {
            $bilanSarQuery->whereRaw("DATE(COALESCE(`date`, created_at)) = ?", [$dateFilter]);
        } elseif ($yearQuarter && $quarter && isset($start, $end)) {
            $bilanSarQuery->whereRaw("DATE(COALESCE(`date`, created_at)) BETWEEN ? AND ?", [$start, $end]);
        } elseif ($yearMonth && $month) {
            $bilanSarQuery->whereRaw("YEAR(COALESCE(`date`, created_at)) = ? AND MONTH(COALESCE(`date`, created_at)) = ?", [$yearMonth, $month]);
        } elseif ($filterYear) {
            $bilanSarQuery->whereRaw("YEAR(COALESCE(`date`, created_at)) = ?", [$filterYear]);
        }

        // Construction d'une clé de cache basée sur l'URL complète des filtres
        $cacheKey = 'rapport_pdf_data_' . md5($request->fullUrl());

        // Mettre en cache les données pendant 10 minutes
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($bilanSarQuery, $request, $dateFilter, $yearQuarter, $quarter, $yearMonth, $month, $filterYear, $start, $end) {

            // Palette de couleurs commune aux graphiques
            $colorsPalette = [
                '#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0',
                '#795548', '#E91E63', '#00BCD4', '#FFEB3B', '#009688'
            ];

            // 1. Graphique : Types d'événements
            $typesData = (clone $bilanSarQuery)
                ->selectRaw('type_d_evenement_id, COUNT(*) as count')
                ->groupBy('type_d_evenement_id')
                ->with('typeEvenement')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->typeEvenement->nom ?? 'Inconnu',
                        'count' => $item->count,
                    ];
                });
            $typesLabels = $typesData->pluck('name')->toArray();
            $typesCounts = $typesData->pluck('count')->toArray();
            $nbBars      = count($typesCounts);
            $colorsUsed  = array_slice($colorsPalette, 0, $nbBars);
            $typesChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $typesLabels,
                    'datasets' => [[
                        'data'            => $typesCounts,
                        'backgroundColor' => $colorsUsed,
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'type'  => 'linear',
                            'min'   => 0,
                            'ticks' => ['beginAtZero' => true]
                        ]
                    ]
                ]
            ];

            // 2. Graphique : Causes d'événements
            $causesData = (clone $bilanSarQuery)
                ->selectRaw('cause_de_l_evenement_id, COUNT(*) as count')
                ->groupBy('cause_de_l_evenement_id')
                ->with('causeEvenement')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->causeEvenement->nom ?? 'Inconnu',
                        'count' => $item->count,
                    ];
                });
            $causesLabels = $causesData->pluck('name')->toArray();
            $causesCounts = $causesData->pluck('count')->toArray();
            $nbBars       = count($causesCounts);
            $colorsUsed   = array_slice($colorsPalette, 0, $nbBars);
            $causesChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $causesLabels,
                    'datasets' => [[
                        'label'           => "Nombre d'événements (Causes)",
                        'data'            => $causesCounts,
                        'backgroundColor' => $colorsUsed,
                    ]]
                ],
                'options' => [
                    'responsive' => true,
                    'scales'   => [
                        'y' => [
                            'type'  => 'linear',
                            'min'   => 0,
                            'ticks' => ['beginAtZero' => true, 'stepSize' => 1]
                        ]
                    ]
                ]
            ];

            // 3. Graphique : Répartition par Région
            $regionsData = (clone $bilanSarQuery)
                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->with('region')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->region->nom ?? 'Inconnu',
                        'count' => $item->count,
                    ];
                });
            $regionsLabels = $regionsData->pluck('name')->toArray();
            $regionsCounts = $regionsData->pluck('count')->toArray();
            $nbBars        = count($regionsCounts);
            $colorsUsed    = array_slice($colorsPalette, 0, $nbBars);
            $regionsChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $regionsLabels,
                    'datasets' => [[
                        'label'           => "Nombre de bilans SAR (Régions)",
                        'data'            => $regionsCounts,
                        'backgroundColor' => $colorsUsed,
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'min'         => 0
                        ]
                    ]
                ]
            ];

            // 4. Graphique : Statistiques des Bilans SAR
            $bilanStats = (clone $bilanSarQuery)
                ->selectRaw('
                    SUM(pob) as pob_total, 
                    SUM(survivants) as survivants_total, 
                    SUM(blesses) as blesses_total, 
                    SUM(morts) as morts_total, 
                    SUM(disparus) as disparus_total, 
                    SUM(evasan) as evasan_total
                ')
                ->first();
            $bilanLabels = ["POB", "Survivants", "Blessés", "Morts", "Disparus", "Evasan"];
            $bilanCounts = [
                $bilanStats->pob_total ?? 0,
                $bilanStats->survivants_total ?? 0,
                $bilanStats->blesses_total ?? 0,
                $bilanStats->morts_total ?? 0,
                $bilanStats->disparus_total ?? 0,
                $bilanStats->evasan_total ?? 0,
            ];
            $bilanChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $bilanLabels,
                    'datasets' => [[
                        'label'           => 'Statistiques des Bilans SAR',
                        'data'            => $bilanCounts,
                        'backgroundColor' => ['#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0', '#795548']
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'min'         => 0
                        ]
                    ]
                ]
            ];

            // 5. Graphique : Nombre d'entrées par Zone
            $zoneCounts = [];
            for ($i = 1; $i <= 9; $i++) {
                $modelClass = "App\\Models\\zone_$i";
                if (class_exists($modelClass)) {
                    $query = $modelClass::query();
                    if ($dateFilter) {
                        $query->whereDate('time_of_fix', $dateFilter);
                    } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                        $query->whereBetween('time_of_fix', [$start, $end]);
                    } elseif ($yearMonth && $month) {
                        $query->whereYear('time_of_fix', $yearMonth)
                            ->whereMonth('time_of_fix', $month);
                    } elseif ($filterYear) {
                        $query->whereYear('time_of_fix', $filterYear);
                    }
                    $zoneCounts["Zone $i"] = $query->count();
                }
            }
            $zoneLabels = array_keys($zoneCounts);
            $zoneValues = array_values($zoneCounts);
            $zoneChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $zoneLabels,
                    'datasets' => [[
                        'label'           => "Nombre d'entrées par zone",
                        'data'            => $zoneValues,
                        'backgroundColor' => '#17a2b8'
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'min'         => 0
                        ]
                    ]
                ]
            ];

            // 6. Graphique : Flags (navires de pêche)
            $flagQuery = Peche::query();
            if ($dateFilter) {
                $flagQuery->whereDate('time_of_fix', $dateFilter);
            } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                $flagQuery->whereBetween('time_of_fix', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $flagQuery->whereYear('time_of_fix', $yearMonth)
                        ->whereMonth('time_of_fix', $month);
            } elseif ($filterYear) {
                $flagQuery->whereYear('time_of_fix', $filterYear);
            }
            $flagData = $flagQuery
                ->selectRaw('flag, COUNT(*) as count')
                ->groupBy('flag')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->flag,
                        'count' => $item->count,
                    ];
                });
            $flagLabels = $flagData->pluck('name')->toArray();
            $flagCounts = $flagData->pluck('count')->toArray();
            $flagChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $flagLabels,
                    'datasets' => [[
                        'label'           => 'Nombre de navires',
                        'data'            => $flagCounts,
                        'backgroundColor' => ['#FF5733', '#33FF57', '#3357FF', '#F3FF33', '#FF33A8'],
                    ]]
                ]
            ];

            // 7. Graphique : ZEE RECAP – Ship Types
            $shipTypesQuery = Article::query();
            if ($dateFilter) {
                $shipTypesQuery->whereDate('time_of_fix', $dateFilter);
            } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                $shipTypesQuery->whereBetween('time_of_fix', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $shipTypesQuery->whereYear('time_of_fix', $yearMonth)
                                ->whereMonth('time_of_fix', $month);
            } elseif ($filterYear) {
                $shipTypesQuery->whereYear('time_of_fix', $filterYear);
            }
            $shipTypesData = $shipTypesQuery
                ->selectRaw('ship_type, COUNT(*) as count')
                ->groupBy('ship_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->ship_type,
                        'count' => $item->count,
                    ];
                });
            $shipTypesLabels = $shipTypesData->pluck('name')->toArray();
            $shipTypesCounts = $shipTypesData->pluck('count')->toArray();
            $topShipTypes    = $shipTypesData->sortByDesc('count')->values()->take(3)->toArray();
            $topShipTypesFlags = [];
            foreach ($topShipTypes as $shipTypeItem) {
                $flagRecord = Article::where('ship_type', $shipTypeItem['name'])
                    ->selectRaw('flag, COUNT(*) as count')
                    ->groupBy('flag')
                    ->orderByDesc('count')
                    ->first();
                $topShipTypesFlags[] = $flagRecord ? $flagRecord->flag : 'Inconnu';
            }
            $shipTypesChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $shipTypesLabels,
                    'datasets' => [[
                        'label'           => 'Nombre de navires par Ship Type',
                        'data'            => $shipTypesCounts,
                        'backgroundColor' => ['#FF5733', '#33FF57', '#3357FF', '#F3FF33', '#FF33A8'],
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'min'         => 0
                        ]
                    ]
                ]
            ];

            // 8. Graphique : Cabotage
            $cabotageQuery = \App\Models\Cabotage::query();
            if ($dateFilter) {
                $cabotageQuery->whereDate('date', $dateFilter);
            } elseif (isset($start, $end)) {
                $cabotageQuery->whereBetween('date', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $cabotageQuery->whereYear('date', $yearMonth)
                            ->whereMonth('date', $month);
            } elseif ($filterYear) {
                $cabotageQuery->whereYear('date', $filterYear);
            }
            $cabotageData = $cabotageQuery
                ->selectRaw('
                    provenance,
                    COUNT(DISTINCT navires) as total_navires,
                    SUM(equipage) as total_equipage,
                    SUM(passagers) as total_passagers
                ')
                ->groupBy('provenance')
                ->get();
            $labels = $cabotageData->pluck('provenance')->toArray();
            $totalNavires   = $cabotageData->pluck('total_navires')->toArray();
            $totalEquipage  = $cabotageData->pluck('total_equipage')->toArray();
            $totalPassagers = $cabotageData->pluck('total_passagers')->toArray();
            $cabotageChartConfig = [
                "type"    => "bar",
                "data"    => [
                    "labels"   => $labels,
                    "datasets" => [
                        [
                            "label"           => "Total Navires",
                            "data"            => $totalNavires,
                            "backgroundColor" => "rgba(75, 192, 192, 0.2)",
                            "borderColor"     => "rgba(75, 192, 192, 1)",
                            "borderWidth"     => 1,
                        ],
                        [
                            "label"           => "Total Equipage",
                            "data"            => $totalEquipage,
                            "backgroundColor" => "rgba(192, 75, 192, 0.2)",
                            "borderColor"     => "rgba(192, 75, 192, 1)",
                            "borderWidth"     => 1,
                        ],
                        [
                            "label"           => "Total Passagers",
                            "data"            => $totalPassagers,
                            "backgroundColor" => "rgba(192, 192, 75, 0.2)",
                            "borderColor"     => "rgba(192, 192, 75, 1)",
                            "borderWidth"     => 1,
                        ],
                    ]
                ],
                "options" => [
                    "scales" => [
                        "yAxes" => [
                            [
                                "ticks" => [
                                    "beginAtZero" => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            // 9. Graphique : Articles par Port
            $articlesForGraph = Article::query();
            if ($dateFilter) {
                $articlesForGraph->whereDate('time_of_fix', $dateFilter);
            } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                $articlesForGraph->whereBetween('time_of_fix', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $articlesForGraph->whereYear('time_of_fix', $yearMonth)
                                ->whereMonth('time_of_fix', $month);
            } elseif ($filterYear) {
                $articlesForGraph->whereYear('time_of_fix', $filterYear);
            }
            // Filtrage sur la colonne "destination" en se basant sur le modèle Destination
            $filtreDestinations = \App\Models\Destination::pluck('name')->toArray();
            $articlesForGraph->where(function ($q) use ($filtreDestinations) {
                foreach ($filtreDestinations as $destination) {
                    $q->orWhere('destination', $destination);
                }
                $q->orWhere('destination', 'LIKE', 'Mg%');
            })
            ->where('ship_type', '!=', 'Tug')
            ->whereNotIn('vessel_name', ['TSARAVATSY', 'AVISOA', 'TS INDIAN OCEAN'])
            ->where('flag', '!=', 'Madagascar');
            $articlesForGraph = $articlesForGraph->get();

            $portCounts = [];
            foreach ($articlesForGraph as $article) {
                $destination = \App\Models\Destination::where('name', $article->destination)->first();
                if ($destination) {
                    foreach ($destination->ports as $port) {
                        $portName = $port->name;
                        if (isset($portCounts[$portName])) {
                            $portCounts[$portName]++;
                        } else {
                            $portCounts[$portName] = 1;
                        }
                    }
                }
            }
            $portLabels = array_keys($portCounts);
            $portValues = array_values($portCounts);
            $portChartConfig = [
                'type'    => 'bar',
                'data'    => [
                    'labels'   => $portLabels,
                    'datasets' => [[
                        'label'           => "Nombre d'articles par Port",
                        'data'            => $portValues,
                        'backgroundColor' => '#9C27B0'
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                        ]
                    ]
                ]
            ];

            // 10. Filtrage Vedette SAR
            $VedetteQuery = Vedette::query();
            if ($dateFilter) {
                $VedetteQuery->whereDate('date', $dateFilter);
            } elseif (isset($start, $end)) {
                $VedetteQuery->whereBetween('date', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $VedetteQuery->whereYear('date', $yearMonth)
                            ->whereMonth('date', $month);
            } elseif ($filterYear) {
                $VedetteQuery->whereYear('date', $filterYear);
            }
            $Vedettes = $VedetteQuery->get();

            // 11. Filtrage Suivi Navire Particulier
            $nav_particulierQuery = \App\Models\SuiviNavireParticulier::query();
            if ($dateFilter) {
                $nav_particulierQuery->whereDate('date', $dateFilter);
            } elseif (isset($start, $end)) {
                $nav_particulierQuery->whereBetween('date', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $nav_particulierQuery->whereYear('date', $yearMonth)
                                    ->whereMonth('date', $month);
            } elseif ($filterYear) {
                $nav_particulierQuery->whereYear('date', $filterYear);
            }
            $nav_particuliers = $nav_particulierQuery->get();

            // 12. Passage Inoffensif
            $passageInoffensifQuery = \App\Models\PassageInoffensif::query();
            if ($dateFilter) {
                $passageInoffensifQuery->whereDate('date_entree', $dateFilter);
            } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                $passageInoffensifQuery->whereBetween('date_entree', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $passageInoffensifQuery->whereYear('date_entree', $yearMonth)
                                    ->whereMonth('date_entree', $month);
            } elseif ($filterYear) {
                $passageInoffensifQuery->whereYear('date_entree', $filterYear);
            }
            $passageInoffensifs = $passageInoffensifQuery->get();

            // Construction des URLs pour chaque graphique
            $typesChartUrl     = 'https://quickchart.io/chart?width=500&height=200&version=3&c=' . urlencode(json_encode($typesChartConfig));
            $causesChartUrl    = 'https://quickchart.io/chart?width=500&height=200&version=3&c=' . urlencode(json_encode($causesChartConfig));
            $regionsChartUrl   = 'https://quickchart.io/chart?width=500&height=200&version=3&c=' . urlencode(json_encode($regionsChartConfig));
            $bilanChartUrl     = 'https://quickchart.io/chart?width=450&height=200&version=3&c=' . urlencode(json_encode($bilanChartConfig));
            $zoneChartUrl      = 'https://quickchart.io/chart?width=450&height=200&version=3&c=' . urlencode(json_encode($zoneChartConfig));
            $flagChartUrl      = 'https://quickchart.io/chart?width=450&height=200&version=3&c=' . urlencode(json_encode($flagChartConfig));
            $shipTypesChartUrl = 'https://quickchart.io/chart?width=500&height=300&version=3&c=' . urlencode(json_encode($shipTypesChartConfig));
            $cabotadeChartUrl  = 'https://quickchart.io/chart?width=500&height=300&version=3&c=' . urlencode(json_encode($cabotageChartConfig));
            $portChartUrl      = 'https://quickchart.io/chart?width=500&height=300&version=3&c=' . urlencode(json_encode($portChartConfig));

            // Appels HTTP asynchrones pour récupérer les images de graphiques
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $promises = [
                'typesChartImage'     => $client->getAsync($typesChartUrl),
                'causesChartImage'    => $client->getAsync($causesChartUrl),
                'regionsChartImage'   => $client->getAsync($regionsChartUrl),
                'bilanChartImage'     => $client->getAsync($bilanChartUrl),
                'zoneChartImage'      => $client->getAsync($zoneChartUrl),
                'flagChartImage'      => $client->getAsync($flagChartUrl),
                'shipTypesChartImage' => $client->getAsync($shipTypesChartUrl),
                'cabotageChartImage'  => $client->getAsync($cabotadeChartUrl),
                'portChartImage'      => $client->getAsync($portChartUrl)
            ];
            $results = \GuzzleHttp\Promise\Utils::unwrap($promises);

            // Conversion des images en base64
            $typesChartBase64     = 'data:image/png;base64,' . base64_encode($results['typesChartImage']->getBody()->getContents());
            $causesChartBase64    = 'data:image/png;base64,' . base64_encode($results['causesChartImage']->getBody()->getContents());
            $regionsChartBase64   = 'data:image/png;base64,' . base64_encode($results['regionsChartImage']->getBody()->getContents());
            $bilanChartBase64     = 'data:image/png;base64,' . base64_encode($results['bilanChartImage']->getBody()->getContents());
            $zoneChartBase64      = 'data:image/png;base64,' . base64_encode($results['zoneChartImage']->getBody()->getContents());
            $flagChartBase64      = 'data:image/png;base64,' . base64_encode($results['flagChartImage']->getBody()->getContents());
            $shipTypesChartBase64 = 'data:image/png;base64,' . base64_encode($results['shipTypesChartImage']->getBody()->getContents());
            $cabotageBase64       = 'data:image/png;base64,' . base64_encode($results['cabotageChartImage']->getBody()->getContents());
            $portChartBase64      = 'data:image/png;base64,' . base64_encode($results['portChartImage']->getBody()->getContents());

            // Récupération des données pour Avurnav et Pollution
            $avurnavQuery = Avurnav::query();
            $pollutionQuery = Pollution::query();
            if ($dateFilter) {
                $avurnavQuery->whereDate('date', $dateFilter);
                $pollutionQuery->whereDate('date', $dateFilter);
            } elseif ($yearQuarter && $quarter && isset($start, $end)) {
                $avurnavQuery->whereBetween('date', [$start, $end]);
                $pollutionQuery->whereBetween('date', [$start, $end]);
            } elseif ($yearMonth && $month) {
                $avurnavQuery->whereYear('date', $yearMonth)
                            ->whereMonth('date', $month);
                $pollutionQuery->whereYear('date', $yearMonth)
                            ->whereMonth('date', $month);
            } elseif ($filterYear) {
                $avurnavQuery->whereYear('date', $filterYear);
                $pollutionQuery->whereYear('date', $filterYear);
            }
            $avurnavs   = $avurnavQuery->get();
            $pollutions = $pollutionQuery->get();

           // Construction du texte récapitulatif du filtre
            if ($dateFilter) {
                $filterResult = " " . $dateFilter;
            } elseif ($yearQuarter && $quarter) {
                $qText = ($quarter == 1) ? "1ER TRIMESTRE" : $quarter . "ÈME TRIMESTRE";
                $filterResult = "ANNEE $yearQuarter - $qText";
            } elseif ($yearMonth && $month) {
                $months = [
                    1 => "janvier", 2 => "février", 3 => "mars", 4 => "avril",
                    5 => "mai", 6 => "juin", 7 => "juillet", 8 => "août",
                    9 => "septembre", 10 => "octobre", 11 => "novembre", 12 => "décembre"
                ];
                $monthName = $months[(int)$month] ?? $month;
                $filterResult = "année $yearMonth - mois de $monthName";
            } else {
                $filterResult = "année " . $filterYear;
            }


            return [
                'filterResult'         => $filterResult,
                'typesData'            => $typesData,
                'typesChartUrl'        => $typesChartUrl,
                'causesData'           => $causesData,
                'causesChartUrl'       => $causesChartUrl,
                'regionsData'          => $regionsData,
                'regionsChartUrl'      => $regionsChartUrl,
                'bilanStats'           => $bilanStats,
                'bilanChartUrl'        => $bilanChartUrl,
                'zoneCounts'           => $zoneCounts,
                'zoneChartUrl'         => $zoneChartUrl,
                'flagData'             => $flagData,
                'flagChartUrl'         => $flagChartUrl,
                'typesChartBase64'     => $typesChartBase64,
                'causesChartBase64'    => $causesChartBase64,
                'regionsChartBase64'   => $regionsChartBase64,
                'bilanChartBase64'     => $bilanChartBase64,
                'zoneChartBase64'      => $zoneChartBase64,
                'flagChartBase64'      => $flagChartBase64,
                'bilans'               => $bilanSarQuery->get(),
                'avurnavs'             => $avurnavs,
                'pollutions'           => $pollutions,
                'shipTypesData'        => $shipTypesData,
                'shipTypesChartBase64' => $shipTypesChartBase64,
                'topShipTypes'         => $topShipTypes,
                'topShipTypesFlags'    => $topShipTypesFlags,
                'cabotageData'         => $cabotageData,
                'cabotageBase64'       => $cabotageBase64,
                'vedettes'             => $Vedettes,
                'passageInoffensifs'   => $passageInoffensifs,
                'nav_particuliers'     => $nav_particuliers,
                'portChartUrl'         => $portChartUrl,
                'portChartBase64'      => $portChartBase64,
                'portCounts'           => $portCounts,
            ];
        });

        // Génération du PDF en activant les images distantes
        $pdf = PDF::loadView('rapport_pdf', $data)
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download('rapport_' . $data['filterResult'] . '.pdf');
    }
}