@extends('general.top')

@section('title', 'LISTES AVURNAV')

@section('content')

<div class="container-fluid px-4">
    <div class="top-menu">
        <button class="btn btn-success">
            <a class="text-decoration-none text-white" href="{{ route('avurnav.create') }}">Créer AVURNAV</a>
        </button>
    </div>
    <h2 class="mb-4 text-center">🚢 Liste des Données AVURNAV</h2>

    {{-- Affichage d'un éventuel message de succès --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Formulaire de filtre --}}
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET">
            <div class="row">
                <!-- Filtre par année -->
                <div class="col-md-3">
                    <label for="year">Année :</label>
                    <input type="number" name="year" id="year" class="form-control" placeholder="YYYY" value="{{ request('year') }}">
                </div>
                <!-- Filtre par mois -->
                <div class="col-md-3">
                    <label for="month">Mois :</label>
                    <input type="number" name="month" id="month" class="form-control" placeholder="MM" min="1" max="12" value="{{ request('month') }}">
                </div>
                <!-- Filtre par jour -->
                <div class="col-md-3">
                    <label for="day">Jour :</label>
                    <input type="number" name="day" id="day" class="form-control" placeholder="DD" min="1" max="31" value="{{ request('day') }}">
                </div>
                <!-- Filtre par intervalle de dates -->
                <div class="col-md-3">
                    <label for="start_date">Date de début :</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3 mt-2">
                    <label for="end_date">Date de fin :</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <!-- Bouton Filtrer -->
                <div class="col-md-3 mt-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
                <!-- Bouton Réinitialiser -->
                <div class="col-md-3 mt-4 d-flex align-items-end">
                    <a href="{{ route('avurnav.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>AVURNAV Local</th>
                    <th>Île</th>
                    <th>Vous signale</th>
                    <th>Position</th>
                    <th>Navire</th>
                    <th>POB</th>
                    <th>Type</th>
                    <th>Caractéristiques</th>
                    <th>Zone</th>
                    <th>Dernière Communication</th>
                    <th>Contacts</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avurnavs as $avurnav)
                    <tr>
                        <td><small>{{ $avurnav->date }}</small></td>
                        <td><small>{{ $avurnav->avurnav_local }}</small></td>
                        <td><small>{{ $avurnav->ile }}</small></td>
                        <td><small>{{ $avurnav->vous_signale }}</small></td>
                        <td><small>{{ $avurnav->position }}</small></td>
                        <td><small>{{ $avurnav->navire }}</small></td>
                        <td><small>{{ $avurnav->pob }}</small></td>
                        <td><small>{{ $avurnav->type }}</small></td>
                        <td><small>{{ $avurnav->caracteristiques }}</small></td>
                        <td><small>{{ $avurnav->zone }}</small></td>
                        <td><small>{{ $avurnav->derniere_communication ?? 'Non disponible' }}</small></td>
                        <td><small>{{ $avurnav->contacts }}</small></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('export.pdf', $avurnav->id) }}" class="btn btn-secondary btn-sm">Exporter</a>
                                <!-- Vous pouvez décommenter ou ajouter d'autres actions ici -->
                                <form action="" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet élément ?');">
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
                        <td colspan="13" class="text-center text-muted">Aucune donnée enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .pagination {
        flex-wrap: wrap; /* Empêche le débordement */
        justify-content: center; /* Centre la pagination */
    }
    .table {
        border-radius: 5px; /* Arrondi des bords du tableau */
        overflow: hidden; /* Assure que les coins restent arrondis */
    }
    .table thead {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
    .table tbody {
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }
</style>
@endsection
