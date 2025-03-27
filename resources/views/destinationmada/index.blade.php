@extends('general.top')

@section('title', 'LISTE DES DESTINATIONS')

@section('content')

<div class="container-fluid px-4">

    <div class="top-menu">
        <button class="btn btn-success">
            <a class="text-decoration-none text-white" href="{{ route('destinations.create') }}">Créer destination</a>
        </button>
    </div>

    <!-- Formulaire de recherche -->
    <div class="mb-4">
        <form method="GET" action="{{ route('destinations.index') }}">
            <div class="input-group" style="max-width: 400px; margin-left: auto;">
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Rechercher une destination" class="form-control">
                <button type="submit" class="btn btn-outline-secondary">Rechercher</button>
            </div>
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
