<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Maestro</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tema global -->
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">

  <style>
    body{
      background: var(--color-white, #FFFFFF);
      color: var(--color-dark, #231a24);
    }

    /* Topbar */
    .app-topbar{
      background: var(--color-dark, #231a24);
      color: var(--color-white, #FFFFFF);
      border-bottom: 1px solid rgba(255,255,255,0.12);
    }

    .app-brand{
      letter-spacing: .2px;
      font-weight: 600;
    }

    /* Cards */
    .card-soft{
      border: 1px solid var(--color-border, rgba(83, 86, 84, 0.28));
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(35,26,36,0.10);
      overflow: hidden;
    }

    .card-soft .card-header{
      background: linear-gradient(
        180deg,
        var(--color-bg-soft, rgba(35, 26, 36, 0.06)),
        rgba(255,255,255,0)
      );
      border-bottom: 1px solid var(--color-border, rgba(83, 86, 84, 0.28));
    }

    /* Buttons */
    .btn-accent{
      background: var(--color-accent, #535654);
      border-color: var(--color-accent, #535654);
      color: var(--color-white, #FFFFFF);
      border-radius: 12px;
      font-weight: 600;
    }

    .btn-accent:hover{
      filter: brightness(0.95);
      color: var(--color-white, #FFFFFF);
    }

    .btn-outline-accent{
      border-color: var(--color-accent, #535654);
      color: var(--color-accent, #535654);
      border-radius: 12px;
      font-weight: 600;
    }

    .btn-outline-accent:hover{
      background: var(--color-accent, #535654);
      color: var(--color-white, #FFFFFF);
    }

    /* Inputs */
    .form-select,
    .form-control{
      border-radius: 12px;
      border-color: var(--color-border, rgba(83, 86, 84, 0.28));
    }

    .form-select:focus,
    .form-control:focus{
      border-color: var(--color-accent, #535654);
      box-shadow: 0 0 0 .25rem rgba(83,86,84,0.18);
    }

    /* Badges */
    .badge-soft{
      background: var(--color-accent-soft, rgba(83, 86, 84, 0.12));
      color: var(--color-dark, #231a24);
      border: 1px solid var(--color-border, rgba(83, 86, 84, 0.28));
      border-radius: 999px;
      font-weight: 600;
      padding: .45rem .65rem;
    }

    /* Table */
    .table thead th{
      background: var(--color-bg-soft, rgba(35, 26, 36, 0.06));
      border-bottom: 1px solid var(--color-border, rgba(83, 86, 84, 0.28));
      color: var(--color-dark, #231a24);
      font-weight: 700;
    }

    .table td,
    .table th{
      border-color: rgba(83,86,84,0.16);
    }

    .table tbody tr:hover{
      background: var(--color-accent-soft, rgba(83, 86, 84, 0.12));
    }

    /* Text helpers */
    .text-muted-2{
      color: rgba(35,26,36,0.65) !important;
    }

    /* Section title */
    .section-title{
      font-weight: 700;
      letter-spacing: .2px;
    }

    /* Divider */
    .divider{
      height: 1px;
      background: var(--color-border, rgba(83, 86, 84, 0.28));
    }

    /* Selected subject box */
    .subject-selected{
      background: rgba(83,86,84,0.06);
      border: 1px solid rgba(83,86,84,0.18);
      border-radius: 12px;
    }
  </style>

</head>

<body>
  <nav class="navbar app-topbar py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex flex-column">
        <span class="app-brand text-white h5 mb-0">Panel del Maestro</span>
        <small class="text-white-50">Gestión por materia y listado de alumnos</small>
      </div>

      <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">Salir</button>
      </form>
      <a href="{{ route('maestro.dashboard') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
        Volver a resumen
      </a>

    </div>
  </nav>

  <main class="container py-4">

    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-5">
        <div class="card card-soft">
          <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted-2 small">Bienvenido</div>
                <div class="h5 mb-0">{{ $teacherName }}</div>
              </div>
              <span class="badge badge-soft">Maestro</span>
            </div>
          </div>
          <div class="card-body">
            <div class="text-muted-2 small">
              Seleccione una materia para consultar alumnos inscritos y administrar su información.
            </div>

            @if ((int)$selectedSubjectId !== 0)
              <div class="mt-3 p-3 subject-selected">
                <div class="text-muted-2 small">Materia seleccionada</div>
                <div class="fw-semibold">{{ $selectedSubjectName }}</div>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="card card-soft">
          <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="section-title">Filtro por materia</div>
              <span class="badge badge-soft">Materias: {{ count($subjects) }}</span>
            </div>
          </div>
          <div class="card-body">
            <form method="GET" action="{{ route('maestro.dashboard.detail') }}" class="row g-2 align-items-end">
              <div class="col-12 col-md-8">
                <label class="form-label mb-1 text-muted-2">Materia</label>
                <select name="subject_id" class="form-select">
                  <option value="0">Seleccione una materia</option>
                  @foreach ($subjects as $s)
                    <option value="{{ $s['id'] }}" @if ((int)$selectedSubjectId === (int)$s['id']) selected @endif>
                      {{ $s['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-4 d-grid">
                <button class="btn btn-accent" type="submit">Aplicar filtro</button>
              </div>

              <div class="col-12">
                <div class="divider my-2"></div>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="{{ route('maestro.dashboard') }}" class="btn btn-outline-accent btn-sm">
                    Limpiar
                  </a>
                  <span class="text-muted-2 small align-self-center">
                    @if ((int)$selectedSubjectId === 0)
                      Sin filtro aplicado
                    @else
                      Mostrando alumnos de: <span class="fw-semibold">{{ $selectedSubjectName }}</span>
                    @endif
                  </span>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="section-title">Alumnos</div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge badge-soft">Total: {{ count($students) }}</span>

            @if ((int)$selectedSubjectId !== 0)
              <button type="button"
                      class="btn btn-accent btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#groupProgressModal">
                Ver gráfica grupal
              </button>
            @endif
          </div>
        </div>
      </div>

      <div class="card-body">
        @if ((int)$selectedSubjectId === 0)
          <div class="text-muted-2">
            Seleccione una materia para visualizar el listado de alumnos.
          </div>
        @else
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 90px;">ID</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  @foreach ($periods as $p)
                    <th class="text-center" style="width: 90px;">P{{ $p->number }}</th>
                  @endforeach
                  <th style="width: 140px;">Promedio</th>
                  <th style="width: 160px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($students as $st)
  <tr data-row="{{ $st['id'] }}">
    <td class="fw-semibold">{{ $st['id'] }}</td>
    <td>{{ $st['name'] }}</td>
    <td class="text-muted-2">{{ $st['email'] }}</td>

    <form method="POST"
          action="{{ route('maestro.grades.updateRow', ['studentId' => $st['id'], 'subjectId' => $selectedSubjectId]) }}"
          class="m-0 row-grade-form">
      @csrf

      @foreach ($periods as $p)
        @php
          $pid = (int)$p->id;
          $val = $st['grades'][$pid] ?? null;
        @endphp

        <td class="text-center">
          <span class="grade-text" data-period="{{ $pid }}">
            @if ($val === null)
              <span class="text-muted-2">—</span>
            @else
              {{ number_format($val, 2) }}
            @endif
          </span>

          <input
            name="grades[{{ $pid }}]"
            type="number"
            step="1"
            min="0"
            max="100"
            value="{{ $val === null ? '' : $val }}"
            class="form-control form-control-sm grade-input d-none text-center"
            data-period="{{ $pid }}"
          >
        </td>
      @endforeach

      <td class="text-center">
        @if ($st['average'] === null)
          <span class="text-muted-2">Sin datos</span>
        @else
          <span class="fw-semibold">{{ number_format($st['average'], 2) }}</span>
        @endif
      </td>

      <td>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-outline-accent btn-sm"
             href="{{ route('maestro.students.show', ['studentId' => $st['id']]) }}">
            Ver ficha
          </a>

          <button type="button" class="btn btn-outline-accent btn-sm btn-edit-row">
            Editar
          </button>

          <button type="submit" class="btn btn-accent btn-sm btn-save-row d-none">
            Guardar
          </button>

          <button type="button" class="btn btn-outline-secondary btn-sm btn-cancel-row d-none">
            Cancelar
          </button>
        </div>
      </td>
    </form>
  </tr>
@empty
  <tr>
    <td colspan="{{ 5 + count($periods) }}" class="text-muted-2">No hay alumnos inscritos en esta materia.</td>
  </tr>
@endforelse
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

  </main>

  <div class="modal fade" id="groupProgressModal" tabindex="-1" aria-labelledby="groupProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
        <div class="modal-header" style="background: var(--color-dark, #231a24); color: var(--color-white, #FFFFFF);">
          <h5 class="modal-title" id="groupProgressModalLabel">Rendimiento grupal por periodo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
              <div class="text-muted-2 small">Materia</div>
              <div class="fw-semibold">{{ $selectedSubjectName }}</div>
            </div>
            <div class="text-muted-2 small">
              Eje X: Periodo (1–6) · Eje Y: Promedio (0–100)
            </div>
          </div>

          <div class="card card-soft">
            <div class="card-body">
              <div style="height: 340px;">
                <canvas id="groupProgressChart"></canvas>
              </div>
              <div id="groupProgressChartEmpty" class="text-muted-2 small mt-2 d-none">
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
  /* Propósito: Alterna una fila entre modo lectura y modo edición. */
  function setRowEditMode(rowEl, isEditing) {
    const textEls = rowEl.querySelectorAll('.grade-text');
    const inputEls = rowEl.querySelectorAll('.grade-input');

    const btnEdit = rowEl.querySelector('.btn-edit-row');
    const btnSave = rowEl.querySelector('.btn-save-row');
    const btnCancel = rowEl.querySelector('.btn-cancel-row');

    textEls.forEach(function (el) {
      el.classList.toggle('d-none', isEditing);
    });

    inputEls.forEach(function (el) {
      el.classList.toggle('d-none', !isEditing);
    });

    btnEdit.classList.toggle('d-none', isEditing);
    btnSave.classList.toggle('d-none', !isEditing);
    btnCancel.classList.toggle('d-none', !isEditing);
  }

  /* Propósito: Restaura valores de inputs usando los valores originales almacenados al entrar a edición. */
  function restoreRowInputs(rowEl) {
    const inputEls = rowEl.querySelectorAll('.grade-input');
    inputEls.forEach(function (input) {
      input.value = input.getAttribute('data-original') || '';
    });
  }

  /* Propósito: Inicializa los listeners de edición por fila en la tabla. */
  function initEditableGradesTable() {
    document.querySelectorAll('tr[data-row]').forEach(function (rowEl) {

      const btnEdit = rowEl.querySelector('.btn-edit-row');
      const btnCancel = rowEl.querySelector('.btn-cancel-row');

      if (!btnEdit || !btnCancel) {
        return;
      }

      /* Propósito: Guardar valores originales al activar edición. */
      btnEdit.addEventListener('click', function () {
        rowEl.querySelectorAll('.grade-input').forEach(function (input) {
          input.setAttribute('data-original', input.value || '');
        });

        setRowEditMode(rowEl, true);
      });

      /* Propósito: Cancelar edición y restaurar valores. */
      btnCancel.addEventListener('click', function () {
        restoreRowInputs(rowEl);
        setRowEditMode(rowEl, false);
      });
    });
  }

    /* Propósito: Serie grupal por periodo (x = periodo, y = promedio). */
  const groupSeries = @json($groupSeries);

  /* Propósito: Estado global de la instancia del chart grupal. */
  let groupChartInstance = null;

  /* Propósito: Obtiene un color CSS variable del tema con fallback seguro. */
  function getThemeColor(varName, fallback) {
    const val = getComputedStyle(document.documentElement).getPropertyValue(varName);
    const cleaned = (val || '').trim();
    return cleaned.length > 0 ? cleaned : fallback;
  }

  /* Propósito: Renderiza la gráfica grupal (periodo vs promedio). */
  function renderGroupChart() {
    const canvas = document.getElementById('groupProgressChart');
    const emptyMsg = document.getElementById('groupProgressChartEmpty');

    if (!groupSeries || groupSeries.length === 0) {
      emptyMsg.classList.remove('d-none');
    } else {
      emptyMsg.classList.add('d-none');
    }

    const accent = getThemeColor('--color-accent', '#535654');

    const data = {
      datasets: [
        {
          label: 'Promedio grupal',
          data: groupSeries,
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
          title: { display: true, text: 'Promedio' },
          min: 0,
          max: 100,
          grid: { color: 'rgba(83,86,84,0.18)' }
        }
      }
    };

    if (groupChartInstance) {
      groupChartInstance.data = data;
      groupChartInstance.options = options;
      groupChartInstance.update();
      return;
    }

    const ctx = canvas.getContext('2d');
    groupChartInstance = new Chart(ctx, {
      type: 'scatter',
      data: data,
      options: options
    });
  }

  /* Propósito: Inicializa el modal de gráfica grupal. */
  function initGroupProgressModal() {
    const modalEl = document.getElementById('groupProgressModal');
    if (!modalEl) {
      return;
    }

    modalEl.addEventListener('shown.bs.modal', function () {
      renderGroupChart();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      if (groupChartInstance) {
        groupChartInstance.destroy();
        groupChartInstance = null;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initEditableGradesTable();
    initGroupProgressModal();
  });
</script>
</body>
</html>
