@extends('general.top')

@section('title', 'LISTES POLLUTION')

@section('content')

<div class="container-fluid px-4">
    <!-- Bloc combiné : Bouton "Créer POLLUTION" et barre de recherche alignés sur la même ligne -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Bouton Créer POLLUTION -->
        <a class="btn btn-success text-white text-decoration-none" href="{{ route('pollutions.create') }}">
            Créer POLLUTION
        </a>
        <!-- Barre de recherche alignée à droite -->
        <form method="GET" action="{{ route('pollutions.index') }}" class="d-flex">
            <input type="text" name="search" id="search" class="form-control me-2" style="width: 300px;" placeholder="Rechercher..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
        </form>
    </div>

    <h2 class="mb-4 text-center">🌫️ Liste des Données POLLUTIONS</h2>

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
                    <a href="{{ route('pollutions.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>N°</th>
                    <th>Zone</th>
                    <th>Coordonnées</th>
                    <th>Type de pollution</th>
                    <th>Image(s)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pollutions as $pollution)
                <tr>
                    <td><small>{{ $pollution->date }}</small></td>
                    <td><small>{{ $pollution->numero }}</small></td>
                    <td><small>{{ $pollution->zone }}</small></td>
                    <td><small>{{ $pollution->coordonnees }}</small></td>
                    <td><small>{{ $pollution->type_pollution }}</small></td>
                    <td>
                        @if ($pollution->images->isNotEmpty())
                            @foreach ($pollution->images as $image)
                                <img src="{{ asset('storage/' . $image->image_path) }}" width="100" class="rounded">
                            @endforeach
                        @else
                            <span class="text-muted">Aucune image</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('pollutions.exportPDF', $pollution->id) }}" class="btn btn-secondary btn-sm">Exporter PDF</a>
                            <form action="{{ route('pollutions.destroy', $pollution->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette pollution ?');">
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
                    <td colspan="7" class="text-center text-muted">Aucune pollution enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .table {
        border-radius: 5px; /* Arrondi des bords du tableau à 5px */
        overflow: hidden; /* Permet de conserver l'arrondi */
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
