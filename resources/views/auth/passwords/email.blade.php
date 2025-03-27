<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mot de passe oublié ?</title>
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
      <h2 class="mb-4 text-center">Mot de passe oublié ?</h2>

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

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
          <label for="email">Votre adresse email :</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Envoyer le lien de réinitialisation</button>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
