@extends('general.top')

@section('title', 'LISTES')

@section('content')

    <div class="container-fluid px-4">
        <h2 class="mb-4 text-center">🐠 ZEE</h2>

        <div class="d-flex justify-content-between flex-wrap mb-3">
            <div>
                <a href="{{ route('articles.export') }}" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Exporter tous les articles
                </a>
            </div>
            <div>
                <a href="{{ route('destinations.index') }}" class="btn btn-primary">
                    Destinations mada
                </a>
                <a href="{{ route('ports.index') }}" class="btn btn-secondary">
                    Ports mada
                </a>
            </div>
        </div>

        <!-- Affichage des messages d'erreur et de succès -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulaire d'import CSV -->
        <div class="card p-3 mb-4 shadow-sm">
            <form action="{{ route('articles.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="csv_file" class="form-control" required>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Importer CSV
                    </button>
                </div>
            </form>
        </div>

        <!-- Barre de recherche et autres actions éventuelles... -->

        <!-- TABLE RESPONSIVE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Flag</th>
                        <th>Vessel Name</th>
                        <th>Registered Owner</th>
                        <th>Call Sign</th>
                        <th>MMSI</th>
                        <th>IMO</th>
                        <th>Ship Type</th>
                        <th>Destination</th>
                        <th>ETA</th>
                        <th>Navigation Status</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Age</th>
                        <th>Time Of Fix</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articles as $article)
                        <tr>
                            <td><small>{{ $article->flag }}</small></td>
                            <td><small>{{ $article->vessel_name }}</small></td>
                            <td><small>{{ $article->registered_owner }}</small></td>
                            <td><small>{{ $article->call_sign }}</small></td>
                            <td><small>{{ $article->mmsi }}</small></td>
                            <td><small>{{ $article->imo }}</small></td>
                            <td><small>{{ $article->ship_type }}</small></td>
                            <td><small>{{ $article->destination }}</small></td>
                            <td><small>{{ $article->eta }}</small></td>
                            <td><small>{{ $article->navigation_status }}</small></td>
                            <td><small>{{ $article->latitude }}</small></td>
                            <td><small>{{ $article->longitude }}</small></td>
                            <td><small>{{ $article->age }}</small></td>
                            <td><small>{{ $article->time_of_fix }}</small></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="{{ route('articles.destroy', $article->id) }}" method="POST">
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
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $articles->links() }}
        </div>
    </div>

    <style>
        .pagination {
            flex-wrap: wrap; /* Empêche le débordement */
            justify-content: center; /* Centre la pagination */
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
