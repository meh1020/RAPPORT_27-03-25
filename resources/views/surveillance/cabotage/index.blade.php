@extends('general.top')

@section('title', 'LISTE DES CABOTAGES')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center mb-4">
        <!-- Bouton Créer CABOTAGE -->
        <div>
            <a href="{{ route('cabotage.create') }}" class="btn btn-success">
                Créer CABOTAGE
            </a>
        </div>
        <!-- Barre de recherche alignée à droite -->
        <div class="ms-auto">
            <form method="GET" action="{{ route('cabotage.index') }}" class="d-flex">
                <input type="text" name="search" id="search" class="form-control me-2" placeholder="Rechercher..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary">Rechercher</button>
            </form>
        </div>
    </div>
    

    <h2 class="mb-4 text-center">⚓ Liste des Cabotages</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Formulaire d'importation CSV --}}
    <div class="card p-3 mb-4">
        <h5>📥 Importer un fichier CSV</h5>
        <form action="{{ route('cabotage.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="csv_file" class="form-control" required accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary">Importer</button>
        </form>
    </div>

    <!-- Formulaire de filtre pour d'autres critères -->
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
                    <a href="{{ route('cabotage.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Provenance</th>
                    <th>Navires</th>
                    <th>Équipage</th>
                    <th>Passagers</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cabotages as $cabotage)
                    <tr>
                        <td><small>{{ $cabotage->date }}</small></td>
                        <td><small>{{ $cabotage->provenance }}</small></td>
                        <td><small>{{ $cabotage->navires }}</small></td>
                        <td><small>{{ $cabotage->equipage }}</small></td>
                        <td><small>{{ $cabotage->passagers }}</small></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('cabotage.destroy', $cabotage->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet élément ?');">
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
                        <td colspan="7" class="text-center text-muted">Aucune donnée enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {{ $cabotages->links() }}
    </div>
</div>

<style>
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    .table {
        border-radius: 5px;
        overflow: hidden;
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
