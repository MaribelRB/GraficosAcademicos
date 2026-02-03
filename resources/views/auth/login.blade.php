<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio de sesión</title>

  <!-- Propósito: Bootstrap base -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Propósito: Íconos Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Propósito: Tema global del proyecto -->
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">

  <style>
    /* Propósito: Fondo y centrado del login similar a la referencia. */
    body{
      min-height: 100vh;
      background:
        radial-gradient(circle at 20% 20%, rgba(255,255,255,0.06), transparent 40%),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.04), transparent 45%),
        linear-gradient(180deg, #0b0f4a 0%, #060831 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      color: var(--color-dark, #231a24);
    }

    /* Propósito: Tarjeta blanca centrada como en la imagen. */
    .login-card{
      width: 100%;
      max-width: 420px;
      background: var(--color-white, #FFFFFF);
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 14px;
      box-shadow: 0 14px 40px rgba(0,0,0,0.25);
      overflow: hidden;
    }

    /* Propósito: Espaciados internos y tipografía del título. */
    .login-card .card-body{
      padding: 26px 26px 24px;
    }

    .login-title{
      text-align: center;
      font-weight: 600;
      letter-spacing: .2px;
      margin-bottom: 18px;
      color: rgba(35,26,36,0.92);
    }

    /* Propósito: Labels como en la referencia. */
    .form-label{
      font-weight: 600;
      color: rgba(35,26,36,0.75);
      margin-bottom: 10px;
    }

    /* Propósito: Inputs redondeados estilo pastilla. */
    .input-pill{
      border-radius: 999px;
      border: 1px solid rgba(83,86,84,0.28);
      padding: 12px 14px;
      background: #fff;
    }

    .input-pill:focus{
      border-color: var(--color-accent, #535654);
      box-shadow: 0 0 0 .25rem rgba(83,86,84,0.18);
    }

    /* Propósito: Input group con ícono a la izquierda. */
    .input-group-text{
      border-radius: 999px 0 0 999px;
      background: rgba(83,86,84,0.08);
      border: 1px solid rgba(83,86,84,0.28);
      border-right: 0;
      color: rgba(35,26,36,0.68);
      padding-left: 14px;
      padding-right: 14px;
    }

    .input-group .form-control{
      border-left: 0;
      border-radius: 0 999px 999px 0;
    }

    /* Propósito: Botón verde/acento similar al de la imagen. */
    .btn-login{
      border-radius: 999px;
      font-weight: 700;
      letter-spacing: .3px;
      padding: 10px 14px;
      background: var(--color-accent, #535654);
      border-color: var(--color-accent, #535654);
      color: var(--color-white, #FFFFFF);
      box-shadow: 0 10px 22px rgba(83,86,84,0.25);
    }

    .btn-login:hover{
      filter: brightness(0.96);
      color: var(--color-white, #FFFFFF);
    }

    /* Propósito: Ajuste de alertas para que no rompan el diseño. */
    .alert{
      border-radius: 12px;
      margin-bottom: 16px;
    }

    /* Propósito: Espaciado extra similar al de la referencia. */
    .field-block{
      margin-bottom: 18px;
    }

    /* Propósito: Ajuste visual del botón mostrar/ocultar contraseña */
    .input-group .input-group-text:last-child{
      border-radius: 0 999px 999px 0;
      color: rgba(35,26,36,0.65);
    }

    .input-group .input-group-text:last-child:hover{
      color: rgba(35,26,36,0.9);
    }
  </style>

  <!-- Propósito: Bootstrap JS -->
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

  <!-- Propósito: Contenedor principal del login -->
  <div class="login-card">
    <div class="card-body">

      <!-- Propósito: Título centrado -->
      <h1 class="login-title h4 mb-3">Inicio de sesión</h1>

      <!-- Propósito: Bloque de errores -->
      @if ($errors->any())
        <div class="alert alert-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <!-- Propósito: Formulario de autenticación -->
      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <!-- Propósito: Campo usuario/correo con ícono -->
        <div class="field-block">
          <label class="form-label">Usuario</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-person"></i>
            </span>
            <input
              name="email"
              type="email"
              class="form-control input-pill"
              placeholder="usuario@correo.com"
              value="{{ old('email') }}"
              required
              autocomplete="username"
            >
          </div>
        </div>

        <!-- Propósito: Campo contraseña con ícono -->
        <div class="field-block">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>

            <input
              id="passwordInput"
              name="password"
              type="password"
              class="form-control input-pill"
              placeholder="••••••••••"
              required
              autocomplete="current-password"
            >

            <button
              type="button"
              class="input-group-text bg-transparent border-start-0"
              id="togglePasswordBtn"
              aria-label="Mostrar u ocultar contraseña"
              style="cursor:pointer;"
            >
              <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
          </div>
        </div>

        <!-- Propósito: Botón de entrada centrado -->
        <button class="btn btn-login w-100" type="submit">ENTRAR</button>

      </form>
    </div>
  </div>

  <script>
  /* Propósito: Alternar visibilidad de la contraseña. */
  document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('passwordInput');
    const btn = document.getElementById('togglePasswordBtn');
    const icon = document.getElementById('togglePasswordIcon');

    if (!input || !btn || !icon) {
      return;
    }

    btn.addEventListener('click', function () {
      const isPassword = input.type === 'password';

      input.type = isPassword ? 'text' : 'password';

      icon.classList.toggle('bi-eye', !isPassword);
      icon.classList.toggle('bi-eye-slash', isPassword);
    });
  });
</script>

</body>
</html>
