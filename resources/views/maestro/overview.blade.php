<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Resumen del Maestro</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">

  <style>
    body{ background: var(--color-white, #FFFFFF); color: var(--color-dark, #231a24); }
    .app-topbar{ background: var(--color-dark, #231a24); color: var(--color-white, #FFFFFF); }
    .card-soft{ border: 1px solid var(--color-border, rgba(83,86,84,.28)); border-radius: 16px; box-shadow: 0 10px 30px rgba(35,26,36,0.10); overflow: hidden; }
    .card-soft .card-header{ background: linear-gradient(180deg, var(--color-bg-soft, rgba(35,26,36,.06)), rgba(255,255,255,0)); border-bottom: 1px solid var(--color-border, rgba(83,86,84,.28)); }
    .badge-soft{ background: var(--color-accent-soft, rgba(83,86,84,.12)); color: var(--color-dark, #231a24); border: 1px solid var(--color-border, rgba(83,86,84,.28)); border-radius: 999px; font-weight: 600; padding: .45rem .65rem; }
    .text-muted-2{ color: rgba(35,26,36,0.65) !important; }
    .subject-card{ cursor:pointer; transition: transform .08s ease; }
    .subject-card:hover{ transform: translateY(-2px); }
  </style>
</head>

<body>
  <nav class="navbar app-topbar py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex flex-column">
        <span class="text-white h5 mb-0">Resumen del Maestro</span>
        <small class="text-white-50">Promedio general por materia</small>
      </div>

      <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">Salir</button>
      </form>
    </div>
  </nav>

  <main class="container py-4">

    <div class="card card-soft mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <div class="text-muted-2 small">Bienvenido</div>
          <div class="h5 mb-0">{{ $teacherName }}</div>
          <div class="text-muted-2 small">Seleccione una materia para entrar al detalle.</div>
        </div>
        <span class="badge badge-soft">Materias: {{ count($cards) }}</span>
      </div>
    </div>

    @if (count($cards) === 0)
      <div class="card card-soft">
        <div class="card-body text-muted-2">No tiene materias asignadas.</div>
      </div>
    @else
      <div class="row g-3">
        @foreach ($cards as $c)
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-soft h-100 subject-card"
                 role="button"
                 tabindex="0"
                 data-subject-id="{{ $c['id'] }}"
                 data-subject-name="{{ $c['name'] }}">
              <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-semibold">{{ $c['name'] }}</div>
                <span class="badge badge-soft">
                  @if ($c['average'] === null)
                    Sin datos
                  @else
                    {{ number_format($c['average'], 2) }}
                  @endif
                </span>
              </div>

              <div class="card-body">
                <div style="height: 220px;">
                  <canvas id="donut_subject_{{ $c['id'] }}"></canvas>
                </div>

                <div class="mt-2 d-flex justify-content-between flex-wrap gap-2 text-muted-2 small">
                  <span>Alumnos: {{ (int)$c['students_count'] }}</span>
                  <span>Califs: {{ (int)$c['grades_count'] }}</span>
                </div>

                <div class="mt-2 text-muted-2 small">
                  Clic para ver detalle y capturar calificaciones.
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </main>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    /* Propósito: Datos de tarjetas para renderizado de donas y navegación al detalle. */
    const cards = @json($cards);

    /* Propósito: Obtiene un color CSS variable del tema con fallback seguro. */
    function getThemeColor(varName, fallback) {
      const val = getComputedStyle(document.documentElement).getPropertyValue(varName);
      const cleaned = (val || '').trim();
      return cleaned.length > 0 ? cleaned : fallback;
    }

    /* Propósito: Renderiza una dona con promedio vs faltante (0..100). */
    function renderDonut(subjectId, avgValue) {
      const canvas = document.getElementById('donut_subject_' + subjectId);
      if (!canvas) {
        return;
      }

      const accent = getThemeColor('--color-accent', '#535654');

      let avg = avgValue === null ? 0 : Number(avgValue);
      if (isNaN(avg)) { avg = 0; }
      if (avg < 0) { avg = 0; }
      if (avg > 100) { avg = 100; }

      const remaining = 100 - avg;

      new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: ['Promedio', 'Restante'],
          datasets: [{
            data: [avg, remaining],
            backgroundColor: [accent, 'rgba(83,86,84,0.18)'],
            borderColor: [accent, 'rgba(83,86,84,0.18)'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '70%',
          plugins: { legend: { display: false } }
        }
      });
    }

    /* Propósito: Inicializa navegación al dashboard detallado por materia. */
    function initSubjectNavigation() {
      document.querySelectorAll('.subject-card').forEach(function (el) {
        const sid = el.getAttribute('data-subject-id');

        function go() {
          const url = new URL("{{ route('maestro.dashboard.detail') }}", window.location.origin);
          url.searchParams.set('subject_id', sid);
          window.location.href = url.toString();
        }

        el.addEventListener('click', go);

        el.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            go();
          }
        });
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      cards.forEach(function (c) {
        renderDonut(c.id, c.average);
      });
      initSubjectNavigation();
    });
  </script>
</body>
</html>
