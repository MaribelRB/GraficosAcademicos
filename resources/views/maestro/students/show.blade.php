<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ficha del Alumno</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">

  <style>
    body{ background: var(--color-white, #FFFFFF); color: var(--color-dark, #231a24); }
    .app-topbar{ background: var(--color-dark, #231a24); color: var(--color-white, #FFFFFF); }
    .card-soft{ border: 1px solid var(--color-border, rgba(83,86,84,.28)); border-radius: 16px; box-shadow: 0 10px 30px rgba(35,26,36,0.10); overflow: hidden; }
    .card-soft .card-header{ background: linear-gradient(180deg, var(--color-bg-soft, rgba(35,26,36,.06)), rgba(255,255,255,0)); border-bottom: 1px solid var(--color-border, rgba(83,86,84,.28)); }
    .badge-soft{ background: var(--color-accent-soft, rgba(83,86,84,.12)); color: var(--color-dark, #231a24); border: 1px solid var(--color-border, rgba(83,86,84,.28)); border-radius: 999px; font-weight: 600; padding: .45rem .65rem; }
    .btn-accent{ background: var(--color-accent, #535654); border-color: var(--color-accent, #535654); color: var(--color-white, #FFFFFF); border-radius: 12px; font-weight: 600; }
    .btn-outline-accent{ border-color: var(--color-accent, #535654); color: var(--color-accent, #535654); border-radius: 12px; font-weight: 600; }
    .btn-outline-accent:hover{ background: var(--color-accent, #535654); color: var(--color-white, #FFFFFF); }
    .table thead th{ background: var(--color-bg-soft, rgba(35,26,36,.06)); border-bottom: 1px solid var(--color-border, rgba(83,86,84,.28)); color: var(--color-dark, #231a24); font-weight: 700; }
    .text-muted-2{ color: rgba(35,26,36,0.65) !important; }
  </style>
</head>

<body>
  <nav class="navbar app-topbar py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex flex-column">
        <span class="text-white h5 mb-0">Ficha del Alumno</span>
        <small class="text-white-50">Materias, periodos y promedios</small>
      </div>

      <div class="d-flex gap-2">
        <a href="{{ route('maestro.dashboard.detail') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">Volver</a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">Salir</button>
        </form>
      </div>
    </div>
  </nav>

  <main class="container py-4">

    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-5">
        <div class="card card-soft">
          <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted-2 small">Alumno</div>
              <div class="h5 mb-0">{{ $student->name }}</div>
              <div class="text-muted-2 small">{{ $student->email }}</div>
            </div>
            <span class="badge badge-soft">ID: {{ $student->id }}</span>
          </div>
          <div class="card-body">
            <div class="text-muted-2 small">
              En esta sección se muestran únicamente materias del alumno que están asignadas al maestro.
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="card card-soft">
          <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold">Resumen</div>
            <span class="badge badge-soft">Materias: {{ count($subjects) }}</span>
          </div>
          <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
              @foreach ($subjects as $s)
                <span class="badge badge-soft">{{ $s->name }}</span>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div class="fw-bold">Calificaciones por materia</div>
        <span class="badge badge-soft">Periodos: {{ count($periods) }}</span>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th style="min-width: 220px;">Materia</th>
                @foreach ($periods as $p)
                  <th class="text-center" style="width: 110px;">P{{ $p->number }}</th>
                @endforeach
                <th class="text-center" style="width: 130px;">Promedio</th>
                <th style="width: 220px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($subjects as $s)
                <tr>
                  <td class="fw-semibold">{{ $s->name }}</td>

                  @foreach ($periods as $p)
                    @php
                      $sid = (int)$s->id;
                      $pid = (int)$p->id;
                      $val = isset($gradesMap[$sid]) && array_key_exists($pid, $gradesMap[$sid]) ? $gradesMap[$sid][$pid] : null;
                    @endphp

                    <td class="text-center">
                      @if ($val === null)
                        <span class="text-muted-2">—</span>
                      @else
                        <span class="fw-semibold">{{ number_format($val, 2) }}</span>
                      @endif
                    </td>
                  @endforeach

                  <td class="text-center">
                    @php $avg = $averages[(int)$s->id] ?? null; @endphp
                    @if ($avg === null)
                      <span class="text-muted-2">Sin datos</span>
                    @else
                      <span class="fw-semibold">{{ number_format($avg, 2) }}</span>
                    @endif
                  </td>
                  <td>
                    <button type="button"
                            class="btn btn-accent btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#progressModal"
                            data-subject-id="{{ $s->id }}"
                            data-subject-name="{{ $s->name }}">
                    Ver progreso (gráfica)
                    </button>

                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-3 text-muted-2 small">
          El promedio se calcula con base en los periodos que ya tienen calificación capturada.
        </div>
      </div>
    </div>

  </main>
  <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header" style="background: var(--color-dark, #231a24); color: var(--color-white, #FFFFFF);">
        <h5 class="modal-title" id="progressModalLabel">Progreso del alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-end mb-2">
          <div class="col-12 col-md-6">
            <label class="form-label text-muted-2 mb-1">Materia</label>
            <select id="progressSubjectSelect" class="form-select">
              @foreach ($subjects as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-6">
            <div class="text-muted-2 small">
              Eje X: Periodos (1–6) · Eje Y: Calificación
            </div>
          </div>
        </div>

        <div class="card card-soft">
          <div class="card-body">
            <div style="height: 340px;">
              <canvas id="progressChart"></canvas>
            </div>
            <div id="progressChartEmpty" class="text-muted-2 small mt-2 d-none">
              No hay calificaciones capturadas para esta materia.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-accent" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
  /* Propósito: Datos de series por materia para la gráfica de progreso. */
  const chartSeriesBySubject = @json($chartSeriesBySubject);

  /* Propósito: Estado global de la instancia del chart. */
  let progressChartInstance = null;

  /* Propósito: Obtiene un color CSS variable del tema con fallback seguro. */
  function getThemeColor(varName, fallback) {
    const val = getComputedStyle(document.documentElement).getPropertyValue(varName);
    const cleaned = (val || '').trim();
    return cleaned.length > 0 ? cleaned : fallback;
  }

  /* Propósito: Construye o actualiza la gráfica de puntos del alumno para una materia. */
  function renderProgressChart(subjectId) {
    const canvas = document.getElementById('progressChart');
    const emptyMsg = document.getElementById('progressChartEmpty');

    const points = chartSeriesBySubject[String(subjectId)] || chartSeriesBySubject[subjectId] || [];

    if (!points || points.length === 0) {
      emptyMsg.classList.remove('d-none');
    } else {
      emptyMsg.classList.add('d-none');
    }

    const accent = getThemeColor('--color-accent', '#535654');

    const data = {
      datasets: [
        {
          label: 'Calificación',
          data: points,
          showLine: true,
          borderColor: accent,
          backgroundColor: accent,
          pointRadius: 4,
          pointHoverRadius: 6,
          tension: 0.25
        }
      ]
    };

    const options = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          type: 'linear',
          title: { display: true, text: 'Periodo' },
          ticks: { stepSize: 1 },
          min: 1,
          max: 6,
          grid: { color: 'rgba(83,86,84,0.18)' }
        },
        y: {
          title: { display: true, text: 'Calificación' },
          min: 0,
          max: 100,
          grid: { color: 'rgba(83,86,84,0.18)' }
        }
      }
    };

    if (progressChartInstance) {
      progressChartInstance.data = data;
      progressChartInstance.options = options;
      progressChartInstance.update();
      return;
    }

    const ctx = canvas.getContext('2d');
    progressChartInstance = new Chart(ctx, {
      type: 'scatter',
      data: data,
      options: options
    });
  }

  /* Propósito: Inicializa el modal para que abra la gráfica de acuerdo a la materia del botón presionado. */
  function initProgressModal() {
    const modalEl = document.getElementById('progressModal');
    const subjectSelect = document.getElementById('progressSubjectSelect');
    const titleEl = document.getElementById('progressModalLabel');

    if (!modalEl || !subjectSelect) {
      return;
    }

    /* Propósito: Al abrir el modal, identificar la materia desde el botón que disparó el modal. */
    modalEl.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      if (!trigger) {
        return;
      }

      const subjectId = trigger.getAttribute('data-subject-id');
      const subjectName = trigger.getAttribute('data-subject-name');

      if (subjectId) {
        subjectSelect.value = subjectId;
        renderProgressChart(subjectId);
      }

      if (titleEl && subjectName) {
        titleEl.textContent = 'Progreso del alumno · ' + subjectName;
      }
    });

    /* Propósito: Si cambia materia en el select, actualizar gráfica y título. */
    subjectSelect.addEventListener('change', function () {
      const subjectId = subjectSelect.value;

      const opt = subjectSelect.options[subjectSelect.selectedIndex];
      const subjectName = opt ? opt.text : '';

      renderProgressChart(subjectId);

      if (titleEl && subjectName) {
        titleEl.textContent = 'Progreso del alumno · ' + subjectName;
      }
    });

    /* Propósito: Destruir chart al cerrar para evitar duplicados y liberar memoria. */
    modalEl.addEventListener('hidden.bs.modal', function () {
      if (progressChartInstance) {
        progressChartInstance.destroy();
        progressChartInstance = null;
      }

      if (titleEl) {
        titleEl.textContent = 'Progreso del alumno';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initProgressModal();
  });
</script>


</body>
</html>
