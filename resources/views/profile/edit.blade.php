@extends('general.top')

@section('title', 'MODIFIER COMPTE')

@section('content')
    <div class="container-fluid px-4">
        <h2>Modifier mes informations</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <!-- Informations de base -->
            <div class="mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Optionnel : Changement de mot de passe -->
            <hr>
            <h4>Modifier le mot de passe</h4>
            <div class="mb-3">
                <label for="current_password" class="form-label">Mot de passe actuel (si vous souhaitez changer)</label>
                <input type="password" name="current_password" id="current_password" class="form-control">
                @error('current_password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
                @error('new_password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
@endsection
