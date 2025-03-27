<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <!-- Inclusion de Bootstrap -->
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
      <h2 class="mb-4 text-center">Changer le mdp</h2>

      @if(session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('password.change') }}">
        @csrf
        <div class="form-group">
          <label for="current_password">Mot de passe actuel :</label>
          <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="new_password">Nouveau mot de passe :</label>
          <input type="password" name="new_password" id="new_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="new_password_confirmation">Confirmer le nouveau mot de passe :</label>
          <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Changer le mot de passe</button>
      </form>
    </div>
  </div>

  <!-- Inclusion des scripts Bootstrap -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
