@extends('general.top')

@section('title', 'SITREP')

@section('content')


<div class="container-fluid px-4">
    <div class="top-menu">
        <button class="btn btn-success">
            <a class="text-decoration-none text-white" href="{{ route('sitreps.create') }}">Créer SITREP</a>
        </button>
    </div>
    <h2 class="mb-4 text-center">📄 Liste des SITREPS</h2>

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
                    <a href="{{ route('sitreps.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>SITREP SAR</th>
                    <th>MRCC Madagascar</th>
                    <th>Event</th>
                    <th>Situation</th>
                    <th>Number of Persons</th>
                    <th>Assistance Required</th>
                    <th>Coordinating RCC</th>
                    <th>Initial Action Taken</th>
                    <th>Chronology</th>
                    <th>Additional Information</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sitreps as $sitrep)
                    <tr>
                        <td><small>{{ $sitrep->date }}</small></td>
                        <td><small>{{ $sitrep->sitrep_sar }}</small></td>
                        <td><small>{{ $sitrep->mrcc_madagascar }}</small></td>
                        <td><small>{{ $sitrep->event }}</small></td>
                        <td><small>{{ $sitrep->situation }}</small></td>
                        <td><small>{{ $sitrep->number_of_persons }}</small></td>
                        <td><small>{{ $sitrep->assistance_required }}</small></td>
                        <td><small>{{ $sitrep->coordinating_rcc }}</small></td>
                        <td><small>{{ $sitrep->initial_action_taken }}</small></td>
                        <td><small>{{ $sitrep->chronology }}</small></td>
                        <td><small>{{ $sitrep->additional_information }}</small></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('sitreps.exportPDF', $sitrep->id) }}" class="btn btn-secondary btn-sm">Exporter</a>
                                <form action="{{ route('sitreps.destroy', $sitrep->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette pollution ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
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
