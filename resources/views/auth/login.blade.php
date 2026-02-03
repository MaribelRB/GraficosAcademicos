<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h5 mb-3">Iniciar sesión</h1>

            @if ($errors->any())
              <div class="alert alert-danger">
                {{ $errors->first() }}
              </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
              @csrf

              <div class="mb-3">
                <label class="form-label">Correo</label>
                <input name="email" type="email" class="form-control" value="{{ old('email') }}" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input name="password" type="password" class="form-control" required>
              </div>

              <button class="btn btn-primary w-100" type="submit">Entrar</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
