@extends('general.top')

@section('title', 'LISTE DES VEDETTE SAR')

@section('content')
<div class="container-fluid px-4">
    <!-- Bloc combiné : Bouton de création et barre de recherche sur une même ligne -->
    <div class="d-flex align-items-center mb-4">
        <!-- Bouton Créer VEDETTE SAR -->
        <div>
            <a class="btn btn-success text-white text-decoration-none" href="{{ route('vedette_sar.create') }}">
                Créer VEDETTE SAR
            </a>
        </div>
        <!-- Barre de recherche alignée à droite -->
        <div class="ms-auto" style="width: 300px;">
            <form method="GET" action="{{ route('vedette_sar.index') }}">
                <div class="input-group">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Rechercher par unité SAR..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    
    

    <h2 class="mb-4 text-center">🛟 Liste des VEDETTE SAR</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulaire de filtre -->
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
                    <a href="{{ route('vedette_sar.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>DATE</th>
                    <th>UNITE SAR</th>
                    <th>TOTAL INTERVENTIONS</th>
                    <th>TOTAL POB</th>
                    <th>TOTAL SURVIVANTS</th>
                    <th>TOTAL MORTS</th>
                    <th>TOTAL DISPARUS</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vedettes as $vedette)
                    <tr>
                        <td><small>{{ $vedette->date }}</small></td>
                        <td><small>{{ $vedette->unite_sar }}</small></td>
                        <td><small>{{ $vedette->total_interventions }}</small></td>
                        <td><small>{{ $vedette->total_pob }}</small></td>
                        <td><small>{{ $vedette->total_survivants }}</small></td>
                        <td><small>{{ $vedette->total_morts }}</small></td>
                        <td><small>{{ $vedette->total_disparus }}</small></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('vedette_sar.destroy', $vedette->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet élément ?');">
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
                        <td colspan="8" class="text-center text-muted">Aucune donnée enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $vedettes->links() }}
    </div>
</div>

<style>
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
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
