@extends('general.top')

@section('title', 'CREER DESTINATION')

@section('content')

<div class="container-fluid px-4">

    <div class="top-menu">
        <button class="btn btn-secondary">
            <a class="text-decoration-none text-white" href="{{ route('destinations.index') }}">Liste des destinations</a>
        </button>
    </div>

    <h2 class="mb-4 text-center">Créer une Destination</h2>
    <form method="POST" action="{{ route('destinations.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Nom de la destination</label>
            <input type="text" name="name" id="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}">
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
    
    
    
</div>
@endsection
