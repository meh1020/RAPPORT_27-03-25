@extends('general.top')

@section('title', 'LISTE DES DESTINATIONS')

@section('content')
<div class="container-fluid px-4">
    <!-- Ligne regroupant les boutons et le formulaire de recherche -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <button class="btn btn-success me-2">
                <a class="text-decoration-none text-white" href="{{ route('destinations.create') }}">Créer destination</a>
            </button>
            <button class="btn btn-info">
                <a class="text-decoration-none text-white" href="#import-section">Importer fichier</a>
            </button>
        </div>
        <div style="max-width: 400px; width: 100%;">
            <form method="GET" action="{{ route('destinations.index') }}">
                <div class="input-group">
                    <input type="text" name="query" value="{{ request('query') }}" placeholder="Rechercher une destination" class="form-control">
                    <button type="submit" class="btn btn-outline-secondary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulaire d'importation -->
    <div id="import-section" class="mb-4">
        <h2 class="mb-4 text-center">Importer un fichier de destinations</h2>
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('destinations.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Fichier texte (.txt)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".txt" required>
            </div>
            <button type="submit" class="btn btn-primary">Importer</button>
        </form>
    </div>
    
    <h2 class="mb-4 text-center">📜 Liste des destinations</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th style="width: 30px;">ID</th>
                    <th style="width: 70px;">Nom</th>
                    <th style="width: 1%; white-space: nowrap;">Actions</th>
                </tr>
            </thead>
            @foreach($destinations as $destination)
            <tr>
                <td style="width: 30px;"><small>{{ $destination->id }}</small></td>
                <td style="width: 70px;"><small>{{ $destination->name }}</small></td>
                <td class="text-center" style="width: 1%; white-space: nowrap;">
                    <form action="{{ route('destinations.destroy', $destination) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
        
    <div class="d-flex justify-content-center mt-3">
        {{ $destinations->links() }}
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
