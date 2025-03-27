@extends('general.top')

@section('title', 'LISTES BILAN SAR')

@section('content')

<div class="container-fluid px-4">

    <!-- Bloc combiné : Bouton d'insertion et barre de recherche alignés sur une même ligne -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Bouton d'insertion -->
        <a class="btn btn-success text-white text-decoration-none" href="{{ route('bilan_sars.create') }}">
            Créer bilan_sars
        </a>
        <!-- Formulaire de recherche aligné à droite -->
        <form method="GET" action="{{ route('bilan_sars.index') }}" class="d-flex">
            <input type="text" name="search" id="search" class="form-control me-2" style="width: 300px;" placeholder="Rechercher..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
        </form>
    </div>

    <h2 class="mb-4 text-center">📋 Liste des Données BILAN SAR</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Formulaire d'importation CSV --}}
    <div class="card p-3 mb-4">
        <h5>📥 Importer un fichier CSV</h5>
        <form action="{{ route('bilan_sars.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="csv_file" class="form-control" required accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary">Importer</button>
        </form>
    </div>

    {{-- Formulaire de filtre --}}
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label for="year">Année :</label>
                    <input type="number" name="year" id="year" class="form-control" placeholder="YYYY"
                           value="{{ request('year') }}">
                </div>
                <div class="col-md-3">
                    <label for="month">Mois :</label>
                    <input type="number" name="month" id="month" class="form-control" placeholder="MM" min="1" max="12"
                           value="{{ request('month') }}">
                </div>
                <div class="col-md-3">
                    <label for="day">Jour :</label>
                    <input type="number" name="day" id="day" class="form-control" placeholder="DD" min="1" max="31"
                           value="{{ request('day') }}">
                </div>
                <div class="col-md-3">
                    <label for="start_date">Date de début :</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3 mt-2">
                    <label for="end_date">Date de fin :</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 mt-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
                <div class="col-md-3 mt-4 d-flex align-items-end">
                    <a href="{{ route('bilan_sars.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped table-fixed">
            <!-- Définition explicite des colonnes -->
            <colgroup>
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-desc-evenement">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-desc-intervention">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
                <col class="col-default">
            </colgroup>
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Nom du Navire</th>
                    <th>Pavillon</th>
                    <th>Immatriculation/Callsign</th>
                    <th>Armateur/Propriétaire</th>
                    <th>Type du Navire</th>
                    <th>Coque</th>
                    <th>Propulsion</th>
                    <th>Moyen d'Alerte</th>
                    <th>Type d'Événement</th>
                    <th>Cause de l'Événement</th>
                    <th>Description de l'Événement</th>
                    <th>Lieu de l'Événement</th>
                    <th>Région</th>
                    <th>Type d'Intervention</th>
                    <th>Description de l'Intervention</th>
                    <th>Source de l'Information</th>
                    <th>POB</th>
                    <th>Survivants</th>
                    <th>Blessés</th>
                    <th>Morts</th>
                    <th>Disparus</th>
                    <th>Évasan</th>
                    <th>Bilan Matériel</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bilans as $bilan)
                    <tr>
                        <td><small>{{ $bilan->date }}</small></td>
                        <td><small>{{ $bilan->nom_du_navire }}</small></td>
                        <td><small>{{ $bilan->pavillon }}</small></td>
                        <td><small>{{ $bilan->immatriculation_callsign }}</small></td>
                        <td class="moyen"><small>{{ $bilan->armateur_proprietaire }}</small></td>
                        <td><small>{{ $bilan->type_du_navire }}</small></td>
                        <td><small>{{ $bilan->coque }}</small></td>
                        <td><small>{{ $bilan->propulsion }}</small></td>
                        <td class="moyen"><small>{{ $bilan->moyen_d_alerte }}</small></td>
                        <td><small>{{ $bilan->typeEvenement->nom ?? '-' }}</small></td>
                        <td><small>{{ $bilan->causeEvenement->nom ?? '-' }}</small></td>
                        <td><small>{{ $bilan->description_de_l_evenement }}</small></td>
                        <td><small>{{ $bilan->lieu_de_l_evenement }}</small></td>
                        <td><small>{{ $bilan->region?->nom }}</small></td>
                        <td><small>{{ $bilan->type_d_intervention }}</small></td>
                        <td><small>{{ $bilan->description_de_l_intervention }}</small></td>
                        <td><small>{{ $bilan->source_de_l_information }}</small></td>
                        <td><small>{{ $bilan->pob }}</small></td>
                        <td><small>{{ $bilan->survivants }}</small></td>
                        <td><small>{{ $bilan->blesses }}</small></td>
                        <td><small>{{ $bilan->morts }}</small></td>
                        <td><small>{{ $bilan->disparus }}</small></td>
                        <td><small>{{ $bilan->evasan }}</small></td>
                        <td><small>{{ $bilan->bilan_materiel }}</small></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('bilan_sars.destroy', $bilan->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet élément ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="26" class="text-center text-muted">Aucune donnée enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="d-flex justify-content-center mt-3">
        {{ $bilans->links() }}
    </div>
</div>
<style>
    .table {
        border-radius: 5px; /* Arrondi des bords du tableau à 5px */
        overflow: hidden; /* Conserve l'arrondi des coins */
    }

    .table thead {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }

    .table tbody {
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }
    .col-desc-evenement, .col-desc-intervention {
        margin-right: 100px !important;
    }
    /* Appliquer une disposition fixe à la table */
    .table-fixed {
        table-layout: fixed;
        width: 100%;
    }
    /* Largeurs pour les colonnes */
    .col-default {
        width: 100px !important;
    }
    .col-desc-evenement,
    .col-desc-intervention {
        width: 500px !important;
    }
    .table-fixed th, .moyen {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
</style>

@endsection
