<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réinitialisation du mot de passe</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
      body {
          background-color: #f4f6f9;
      }
      .form-container {
          max-width: 500px;
          margin: 80px auto;
          background: #fff;
          padding: 30px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-container">
      <h2 class="mb-4 text-center">Réinitialiser votre mot de passe</h2>

      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
          <label for="email">Email :</label>
          <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Nouveau mot de passe :</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirmer le nouveau mot de passe :</label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success btn-block">Réinitialiser le mot de passe</button>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
