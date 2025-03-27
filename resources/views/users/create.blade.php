@extends('general.top')

@section('title', 'CREER COMPTE')

@section('content')

<div class="container-fluid px-4">
    <div class="container">
        <h2 class="mb-4">Créer un compte</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_super_admin" name="is_super_admin" value="1">
                <label for="is_super_admin" class="form-check-label">Superadmin</label>
            </div>

            <button type="submit" class="btn btn-primary">Créer</button>
        </form>
    </div>
</div>

@endsection
