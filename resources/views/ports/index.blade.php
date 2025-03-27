@extends('general.top')

@section('title', 'LISTES PORTS')

@section('content')

<div class="container-fluid px-4">


    <div class="top-menu">
        <button class="btn btn-success">
            <a class="text-decoration-none text-white" href="{{ route('ports.create') }}">Créer PORT</a>
        </button>
    </div>
    <h2 class="mb-4 text-center">🛥️ Liste des Données PORTS</h2>

    <!-- Formulaire de recherche -->
    <div class="mb-4">
        <form method="GET" action="{{ route('ports.index') }}">
            <div class="input-group" style="max-width: 400px; margin-left: auto;">
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Rechercher une port" class="form-control">
                <button type="submit" class="btn btn-outline-secondary">Rechercher</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="container-fluid">
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Nom du port</th>
                    <th>Destinations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ports as $port)
                <tr>
                    <td>{{ $port->name }}</td>
                    <td>{{ $port->destinations->pluck('name')->join(', ') }}</td>
                    <td>
                        <a href="{{ route('ports.edit', $port) }}" class="btn btn-warning">Modifier</a>
                        <form action="{{ route('ports.destroy', $port) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" onclick="return confirm('Supprimer ce port ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
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
