@extends('general.top')

@section('title', 'EDITER UN PORT')

@section('content')


<div class="container-fluid px-4">
    <div class="top-menu">
        <button class="btn btn-success">
            <a class="text-decoration-none text-white" href="{{ route('ports.index') }}">Listes des PORTS</a>
        </button>
    </div>
    <h2 class="mb-4 text-center">🚢 Editer un port</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    <div class="container">
        <form action="{{ route('ports.update', $port) }}" method="POST">
            @csrf
            @method('PUT')
    
            <div class="mb-3">
                <label for="name" class="form-label">Nom du Port</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $port->name }}" required>
            </div>
    
            <div class="mb-3">
                <label class="form-label">Destinations</label>
                <div class="row">
                    @php
                        // Regroupement des destinations par tranche de 10
                        $chunks = $destinations->chunk(10);
                        $nbColumns = $chunks->count();
            
                        // Définition de la classe Bootstrap en fonction du nombre de colonnes
                        switch($nbColumns) {
                            case 1:
                                $colClass = 'col-12';
                                break;
                            case 2:
                                $colClass = 'col-md-6';
                                break;
                            case 3:
                                $colClass = 'col-md-4';
                                break;
                            case 4:
                                $colClass = 'col-md-3';
                                break;
                            default:
                                $colClass = 'col';
                        }
                    @endphp
            
                    @foreach($chunks as $chunk)
                        <div class="{{ $colClass }}">
                            @foreach($chunk as $destination)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="destinations[]" value="{{ $destination->id }}" 
                                           id="destination-{{ $destination->id }}"
                                           {{ $port->destinations->contains($destination->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="destination-{{ $destination->id }}">
                                        {{ $destination->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>            
    
            <button type="submit" class="btn btn-success">Mettre à jour</button>
            <a href="{{ route('ports.index') }}" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    
</div>

@endsection