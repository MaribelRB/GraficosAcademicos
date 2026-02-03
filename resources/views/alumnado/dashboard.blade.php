<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel del Alumno</title>

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
    .text-muted-2{ color: rgba(35,26,36,0.65) !important; }
    .progress{ height: 16px; border-radius: 999px; background: rgba(83,86,84,.14); }
    .progress-bar{ background: var(--color-accent, #535654); border-radius: 999px; }
  </style>
</head>

<body>
  <nav class="navbar app-topbar py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex flex-column">
        <span class="text-white h5 mb-0">Panel del Alumno</span>
        <small class="text-white-50">Promedios y desempeño por materia</small>
      </div>
      <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">Salir</button>
      </form>
    </div>
  </nav>

  <main class="container py-4">

    <div class="row g-3 mb-3">
      <div class="col-12">
        <div class="card card-soft">
          <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <div class="text-muted-2 small">Bienvenido</div>
              <div class="h5 mb-0">{{ $studentName }}</div>
            </div>
            <span class="badge badge-soft">
              Promedio general:
              @if ($generalAverage === null)
                Sin datos
              @else
                {{ number_format($generalAverage, 2) }}
              @endif
            </span>
          </div>

          <div class="card-body">
            @php
              $ga = $generalAverage === null ? 0 : (float)$generalAverage;
              if ($ga < 0) { $ga = 0; }
              if ($ga > 100) { $ga = 100; }
            @endphp
            @if (($viewerRole ?? '') === 'padre')
              <div class="card card-soft mb-3">
                <div class="card-body">
                  <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-8">
                      <label class="form-label text-muted-2 mb-1">Alumno</label>
                      <select id="parentStudentSelect" class="form-select">
                        @foreach ($parentStudents as $st)
                          <option value="{{ $st['id'] }}" @if ((int)$selectedStudentId === (int)$st['id']) selected @endif>
                            {{ $st['name'] }} ({{ $st['email'] }})
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-12 col-md-4 d-grid">
                      <button type="button" id="parentStudentGoBtn" class="btn btn-accent">
                        Ver alumno
                      </button>
                    </div>
                  </div>

                  <div class="text-muted-2 small mt-2">
                    Cambie el alumno para consultar su promedio y desempeño por materia.
                  </div>
                </div>
              </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="text-muted-2 small">Progreso general</div>
              <div class="fw-semibold">
                @if ($generalAverage === null)
                  0%
                @else
                  {{ number_format($ga, 0) }}%
                @endif
              </div>
            </div>

            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int)$ga }}">
              <div class="progress-bar" style="width: {{ (int)$ga }}%"></div>
            </div>

            <div class="mt-2 text-muted-2 small">
              El promedio general se calcula con base en todas las calificaciones capturadas.
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div class="fw-bold">Promedio por materia</div>
        <span class="badge badge-soft">Materias: {{ count($subjectCards) }}</span>
      </div>

      <div class="card-body">
        @if (count($subjectCards) === 0)
          <div class="text-muted-2">No hay materias inscritas.</div>
        @else
          <div class="row g-3">
            @foreach ($subjectCards as $card)
              <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-soft h-100 subject-card"
                  role="button"
                  tabindex="0"
                  data-subject-id="{{ $card['id'] }}"
                  data-subject-name="{{ $card['name'] }}"
                  style="cursor:pointer;">
                  <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted-2 small mt-1">Clic para ver detalle</div>
                    <div class="fw-semibold">{{ $card['name'] }}</div>
                    <span class="badge badge-soft">
                      @if ($card['average'] === null)
                        Sin datos
                      @else
                        {{ number_format($card['average'], 2) }}
                      @endif
                    </span>
                  </div>

                  <div class="card-body">
                    <div style="height: 220px;">
                      <canvas id="pie_subject_{{ $card['id'] }}"></canvas>
                    </div>

                    <div class="mt-2 text-muted-2 small">
                      Periodos capturados: {{ (int)$card['graded_count'] }}
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div class="card card-soft mt-3 d-none" id="subjectDetailCard">
      <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <div class="text-muted-2 small">Materia seleccionada</div>
          <div class="fw-bold" id="detailSubjectName">—</div>
        </div>
        <span class="badge badge-soft" id="detailSubjectAvg">Promedio: —</span>
      </div>

      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <div class="card card-soft h-100">
              <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Calificaciones por periodo</div>
                <span class="badge badge-soft" id="detailPeriodsCount">—</span>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead>
                      <tr>
                        <th style="width: 120px;">Periodo</th>
                        <th class="text-center">Calificación</th>
                      </tr>
                    </thead>
                    <tbody id="detailTableBody">
                      <tr>
                        <td colspan="2" class="text-muted-2">Seleccione una materia.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="text-muted-2 small mt-2">
                  El promedio se calcula con periodos capturados.
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card card-soft h-100">
              <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Progreso (gráfica)</div>
                <span class="badge badge-soft">X: Periodo · Y: Calificación</span>
              </div>
              <div class="card-body">
                <div style="height: 300px;">
                  <canvas id="subjectProgressChart"></canvas>
                </div>
                <div id="subjectProgressEmpty" class="text-muted-2 small mt-2 d-none">
                  No hay calificaciones capturadas para esta materia.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    /* Propósito: Datos para dashboard interactivo del alumno. */
    const subjectCards = @json($subjectCards);
    const periods = @json($periods);
    const gradesMap = @json($gradesMap);
    const averages = @json($averages);

    /* Propósito: Estado del chart de progreso. */
    let subjectChartInstance = null;

    /* Propósito: Cache de charts de dona para evitar recrearlos múltiples veces. */
    const donutCharts = {};

    /* Propósito: Obtiene un color CSS variable del tema con fallback seguro. */
    function getThemeColor(varName, fallback) {
      const val = getComputedStyle(document.documentElement).getPropertyValue(varName);
      const cleaned = (val || '').trim();
      return cleaned.length > 0 ? cleaned : fallback;
    }

    /* Propósito: Renderiza una gráfica de dona con promedio vs faltante (0..100). */
    function renderSubjectPie(subjectId, avgValue) {
      const canvas = document.getElementById('pie_subject_' + subjectId);
      if (!canvas) {
        return;
      }

      const accent = getThemeColor('--color-accent', '#535654');

      let avg = avgValue === null ? 0 : Number(avgValue);
      if (isNaN(avg)) { avg = 0; }
      if (avg < 0) { avg = 0; }
      if (avg > 100) { avg = 100; }

      const remaining = 100 - avg;

      /* Propósito: Si ya existe el chart, solo actualizar datos. */
      if (donutCharts[String(subjectId)]) {
        donutCharts[String(subjectId)].data.datasets[0].data = [avg, remaining];
        donutCharts[String(subjectId)].update();
        return;
      }

      donutCharts[String(subjectId)] = new Chart(canvas.getContext('2d'), {
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
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return ctx.label + ': ' + Number(ctx.parsed).toFixed(2);
                }
              }
            }
          }
        }
      });
    }

    /* Propósito: Renderiza tabla de periodos para una materia. */
    function renderSubjectPeriodsTable(subjectId) {
      const tbody = document.getElementById('detailTableBody');
      tbody.innerHTML = '';

      const sid = String(subjectId);
      let captured = 0;

      periods.forEach(function (p) {
        const pid = String(p.id);
        const periodLabel = 'P' + p.number;

        const has = gradesMap[sid] && Object.prototype.hasOwnProperty.call(gradesMap[sid], pid);
        const val = has ? gradesMap[sid][pid] : null;

        if (val !== null && val !== undefined) {
          captured++;
        }

        const tr = document.createElement('tr');

        const tdP = document.createElement('td');
        tdP.textContent = periodLabel;

        const tdV = document.createElement('td');
        tdV.className = 'text-center';

        if (val === null || val === undefined) {
          tdV.innerHTML = '<span class="text-muted-2">—</span>';
        } else {
          tdV.innerHTML = '<span class="fw-semibold">' + Number(val).toFixed(0) + '</span>';
        }

        tr.appendChild(tdP);
        tr.appendChild(tdV);
        tbody.appendChild(tr);
      });

      const badge = document.getElementById('detailPeriodsCount');
      badge.textContent = 'Capturados: ' + captured + '/' + periods.length;
    }

    /* Propósito: Renderiza gráfica de progreso (puntos) para una materia. */
    function renderSubjectProgressChart(subjectId) {
      const sid = String(subjectId);
      const emptyMsg = document.getElementById('subjectProgressEmpty');
      const canvas = document.getElementById('subjectProgressChart');

      const points = [];

      periods.forEach(function (p) {
        const pid = String(p.id);

        if (gradesMap[sid] && Object.prototype.hasOwnProperty.call(gradesMap[sid], pid)) {
          const val = gradesMap[sid][pid];
          if (val !== null && val !== undefined) {
            points.push({ x: Number(p.number), y: Number(val) });
          }
        }
      });

      if (points.length === 0) {
        emptyMsg.classList.remove('d-none');
      } else {
        emptyMsg.classList.add('d-none');
      }

      const accent = getThemeColor('--color-accent', '#535654');

      const data = {
        datasets: [{
          label: 'Calificación',
          data: points,
          showLine: true,
          borderColor: accent,
          backgroundColor: accent,
          pointRadius: 4,
          pointHoverRadius: 6,
          tension: 0.25
        }]
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

      if (subjectChartInstance) {
        subjectChartInstance.data = data;
        subjectChartInstance.options = options;
        subjectChartInstance.update();
        return;
      }

      subjectChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'scatter',
        data: data,
        options: options
      });
    }

    /* Propósito: Actualiza el header del detalle (nombre y promedio). */
    function renderDetailHeader(subjectId, subjectName) {
      const nameEl = document.getElementById('detailSubjectName');
      const avgEl = document.getElementById('detailSubjectAvg');

      nameEl.textContent = subjectName || '—';

      const sid = String(subjectId);
      const avg = Object.prototype.hasOwnProperty.call(averages, sid) ? averages[sid] : null;

      if (avg === null || avg === undefined) {
        avgEl.textContent = 'Promedio: Sin datos';
      } else {
        avgEl.textContent = 'Promedio: ' + Number(avg).toFixed(2);
      }
    }

    /* Propósito: Selecciona una materia desde el click en una tarjeta. */
    function selectSubject(subjectId, subjectName) {
      const detail = document.getElementById('subjectDetailCard');
      detail.classList.remove('d-none');

      renderDetailHeader(subjectId, subjectName);
      renderSubjectPeriodsTable(subjectId);

      if (subjectChartInstance) {
        subjectChartInstance.destroy();
        subjectChartInstance = null;
      }
      renderSubjectProgressChart(subjectId);

      /* Propósito: Resaltar tarjeta seleccionada. */
      document.querySelectorAll('.subject-card').forEach(function (el) {
        el.style.outline = '';
      });

      const active = document.querySelector('.subject-card[data-subject-id="' + subjectId + '"]');
      if (active) {
        active.style.outline = '2px solid rgba(83,86,84,0.55)';
        active.style.outlineOffset = '2px';
      }
    }

    /* Propósito: Inicializa donas e interacción del dashboard. */
    function initDashboard() {
      /* Propósito: Renderizar todas las donas. */
      subjectCards.forEach(function (c) {
        renderSubjectPie(c.id, c.average);
      });

      /* Propósito: Configurar click y teclado en tarjetas. */
      document.querySelectorAll('.subject-card').forEach(function (cardEl) {
        const sid = cardEl.getAttribute('data-subject-id');
        const sname = cardEl.getAttribute('data-subject-name') || '';

        cardEl.addEventListener('click', function () {
          selectSubject(sid, sname);
        });

        cardEl.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectSubject(sid, sname);
          }
        });
      });

      /* Propósito: Auto-seleccionar primera materia si existe. */
      const first = document.querySelector('.subject-card');
      if (first) {
        const sid = first.getAttribute('data-subject-id');
        const sname = first.getAttribute('data-subject-name') || '';
        selectSubject(sid, sname);
      }
    }

    function initParentStudentSwitcher() {
      const selectEl = document.getElementById('parentStudentSelect');
      const btnEl = document.getElementById('parentStudentGoBtn');

      if (!selectEl || !btnEl) {
        return;
      }

      function go() {
        const studentId = selectEl.value;
        const url = new URL(window.location.href);
        url.searchParams.set('student_id', studentId);
        window.location.href = url.toString();
      }

      btnEl.addEventListener('click', go);

      selectEl.addEventListener('change', function () {
        go();
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      initParentStudentSwitcher();
      initDashboard();
    });
  </script>

</body>
</html>
