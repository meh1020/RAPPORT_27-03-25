@extends('general.top')

@section('title', 'LISTES DES SUIVI DES NAVIRES PARTICULIERS')

@section('content')

<div class="container-fluid px-4">
    <!-- Bloc combiné : Bouton d'insertion et formulaire de recherche -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Bouton d'insertion -->
        <div>
            <a class="btn btn-success text-white text-decoration-none" href="{{ route('suivi_navire_particuliers.create') }}">
                Inserer nav particulier
            </a>
        </div>
        <!-- Formulaire de recherche -->
        <div>
            <form method="GET" action="{{ route('suivi_navire_particuliers.index') }}" class="d-flex">
                <input type="text" name="search" id="search" class="form-control me-2" placeholder="Rechercher par navire, MMSI ou observations..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary">Rechercher</button>
            </form>
        </div>
    </div>

    <h2 class="mb-4 text-center">⛴️ Liste des Données de suivi des navires particuliers</h2>

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
                    <a href="{{ route('suivi_navire_particuliers.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Nom du Navire</th>
                    <th>MMSI</th>
                    <th>Observations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suivis as $suivi)
                    <tr>
                        <td>{{ $suivi->date }}</td>
                        <td>{{ $suivi->nom_navire }}</td>
                        <td>{{ $suivi->mmsi }}</td>
                        <td>{{ $suivi->observations }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('suivi_navire_particuliers.destroy', $suivi->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce suivi ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucun suivi enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
</style>

@endsection
