{{-- resources/views/dashboards.blade.php --}}
@extends('home')

@section('content')

{{-- ✅ Estilos del overlay (pantalla completa) --}}
<style>
  .dash-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: none;           /* oculto por defecto */
    align-items: center;
    justify-content: center;
    z-index: 2000;           /* por encima del contenido */
  }
  .dash-overlay__box {
    color: #fff;
    text-align: center;
    padding: 24px 28px;
    border-radius: 12px;
    background: rgba(0,0,0,.35);
    backdrop-filter: blur(2px);
    min-width: 240px;
  }
  .dash-overlay.show { display: flex; }
</style>

<div class="row">
    <div class="col-xl-8 col-md-6 mb-4">
        {{-- ✅ Agrego id al form y al botón limpiar para enganchar el overlay --}}
        <form id="dash-filter-form" method="GET" action="{{ route('dashboards') }}">
            <div class="row g-3 mb-0 align-items-center">
                <div class="col-sm-auto">
                    <div class="input-group">
                        <span class="mt-2" style="padding-right: 10px;">Desde:</span>
                        <input type="date"
                               id="desde"
                               name="desde"
                               value="{{ old('desde', $desde ?? \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}"
                               class="form-control border-0 minimal-border dash-filter-picker shadow"
                               data-provider="flatpickr"
                               data-range-date="true"
                               data-date-format="d M, Y">
                    </div>
                </div>
                <div class="col-sm-auto">
                    <div class="input-group">
                        <span class="mt-2" style="padding-right: 10px;">Hasta:</span>
                        <input type="date"
                               id="hasta"
                               name="hasta"
                               value="{{ old('hasta', $hasta ?? \Carbon\Carbon::now()->toDateString()) }}"
                               class="form-control border-0 minimal-border dash-filter-picker shadow"
                               data-provider="flatpickr"
                               data-range-date="true"
                               data-date-format="d M, Y">
                    </div>
                </div>
                <div class="col-auto">
                    <button id="btnConsultar" class="btn btn-soft-success material-shadow-none">
                        Consultar Recaudación
                    </button>
                    <a id="btnLimpiar" href="{{ route('dashboards') }}" class="btn btn-soft-primary material-shadow-none">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        @error('hasta') <div class="text-danger mt-2">{{ $message }}</div> @enderror

        <hr>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate alert alert-info material-shadow" style="padding:0px;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="row">
                            <div class="col-xl-5 col-md-6">
                                <p class="text-uppercase fw-medium text-truncate mb-0 mt-2 fs-18"> Total Recaudado </p>
                            </div>
                            <div class="col-xl-7 col-md-6">
                                <!--<div class="card mt-3">
                                  <div class="card-body">
                                    <h6 class="text-muted mb-1">Total general</h6>
                                    <div
                                      id="total-general"
                                      class="fs-3 fw-bold"
                                      data-desde="{{ $desde ?? '' }}"
                                      data-hasta="{{ $hasta ?? '' }}"
                                    >
                                      {{ number_format($totalGeneral ?? 0, 2, '.', '') }}
                                    </div>
                                    <small id="total-hint" class="text-muted d-block mt-1"></small>
                                  </div>
                                </div>-->
                                <h4 class="fs-22 fw-semibold ff-secondary mt-2">
                                    S/.
                                    <span class="counter-value" data-target="{{ number_format($totalGeneral ?? 0, 2, '.', '') }}">0</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

    <div class="col-xl-6 col-md-12">
        <div class="card">
            <div class="card-header alert alert-success alert-dismissible alert-label-icon rounded-label fade show material-shadow">
                <i class=" ri-home-2-line label-icon"></i>
                <h5 class="mb-0">RECAUDACIÓN POR CATEGORÍA</h5>
                <hr class="my-1">
            </div>
            <div class="card-body">
                <div id="chart-pie" data-colors='["--vz-primaryclaro", "--vz-dangerclaro", "--vz-successclaro", "--vz-warning",  "--vz-info"]' class="e-charts aspect-4-3"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12">
        <div class="card">
            <div class="card-header alert alert-success alert-dismissible alert-label-icon rounded-label fade show material-shadow">
                <i class=" ri-home-2-line label-icon"></i>
                <h5 class="mb-0">TUPA/TUSNE/CUIS</h5>
                <hr class="my-1">
            </div>
            <div class="card-body">
                <div id="chart-doughnut" data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]' class="e-charts aspect-4-3"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header alert alert-success alert-dismissible alert-label-icon rounded-label fade show material-shadow">
                <i class=" ri-home-2-line label-icon"></i>
                <h5 class=" mb-0">RECAUDACIÓN POR GERENCIA</h5>
                <hr class="my-1">
            </div>
            <div class="card-body">
                <div id="chart-bar-label-rotation" data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger"]' class="e-charts"></div>
            </div>
        </div>
    </div>

    <div id="dependencias-container" class=" row col-xl-12 g-3" style="display:none;margin-left: 0px;"></div>

    <div class="row mt-4" style="margin-left: 0px;">
        <div class="col-xl-12">
            <div id="detalle-global" ></div>
        </div>
    </div>
</div> <!-- end row-->

{{-- ✅ Overlay pantalla completa (solo uno, global) --}}
<div id="dash-overlay" class="dash-overlay" aria-hidden="true">
  <div class="dash-overlay__box">
    <div class="spinner-border" role="status" aria-hidden="true"></div>
    <div class="mt-3">Cargando datos...</div>
  </div>
</div>

{{-- ✅ Script: muestra overlay al enviar el form o al limpiar --}}
<script>
  (function() {
    const form    = document.getElementById('dash-filter-form');
    const limpiar = document.getElementById('btnLimpiar');
    const overlay = document.getElementById('dash-overlay');

    function showOverlay() {
      overlay?.classList.add('show');
    }

    // Mostrar overlay cuando se envía el formulario (GET)
    if (form) {
      form.addEventListener('submit', function() {
        showOverlay();
        // No preventDefault: dejamos que navegue y el overlay se mantiene
        // sobre la vista actual hasta que llegue la respuesta
      });
    }

    // Mostrar overlay al hacer clic en "Limpiar"
    if (limpiar) {
      limpiar.addEventListener('click', function() {
        showOverlay();
        // La navegación borra el DOM actual y el overlay ya no es necesario
      });
    }

    // (Opcional) mostrar overlay para cualquier navegación saliente
    // window.addEventListener('beforeunload', showOverlay);
  })();

  (function () {
    const overlay = document.getElementById('dash-overlay');

    // Si venimos del login, mostrar overlay de entrada
    try {
      if (sessionStorage.getItem('showDashOverlay') === '1') {
        overlay?.classList.add('show');
      }
    } catch (e) {}

    // Ocultarlo solo cuando la página terminó de cargar COMPLETAMENTE
    window.addEventListener('load', function () {
      try { sessionStorage.removeItem('showDashOverlay'); } catch (e) {}
      overlay?.classList.remove('show');
    });
  })();



///-------------------------------Actualizacion de monto----------------------------------------------

(function(){
  // ===== Config =====
  const INTERVAL_MS = 30000;            // 30 s
  const IDS = { desde:'desde', hasta:'hasta', rango:'rango-fechas', hint:'total-hint' };
  // ==================

  const $hint  = document.getElementById(IDS.hint);
  const $desde = document.getElementById(IDS.desde);
  const $hasta = document.getElementById(IDS.hasta);
  const $rango = document.getElementById(IDS.rango);

  // --- Helpers de fecha ---
  const SOD = d => (d = new Date(d), d.setHours(0,0,0,0), d);
  const EOD = d => (d = new Date(d), d.setHours(23,59,59,999), d);
  const TODAY = () => SOD(new Date());

  function parseDate(v){
    if(!v) return null;
    if(/^\d{4}-\d{2}-\d{2}$/.test(v)) return new Date(v+'T00:00:00');   // YYYY-MM-DD
    const m = v.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);       // dd/mm/yyyy
    if (m) return new Date(`${m[3]}-${m[2].padStart(2,'0')}-${m[1].padStart(2,'0')}T00:00:00`);
    const d = new Date(v); return isNaN(d) ? null : d;
  }

  function getRange(){
    if ($desde && $hasta && ($desde.value || $hasta.value)) {
      return { d: parseDate($desde.value), h: parseDate($hasta.value) };
    }
    if ($rango && $rango.value) {
      // Soporta "dd/mm/yyyy a dd/mm/yyyy", "yyyy-mm-dd to yyyy-mm-dd" o " - " como separador
      const parts = $rango.value.trim().split(/\s*(?:a|to|-)\s*/i);
      if (parts.length >= 2) return { d: parseDate(parts[0]), h: parseDate(parts[1]) };
    }
    return { d: null, h: null };
  }

  function todayInRange(){
    const { d, h } = getRange();
    if (!d || !h) return false;
    const t = TODAY();
    return SOD(d) <= t && t <= EOD(h);
  }

  // --- Recarga condicional ---
  let timer = null;

  function stop(){
    if (timer) { clearInterval(timer); timer = null; }
  }

  function tick(){
    const ok = todayInRange();
    if ($hint) $hint.textContent = ok
      ? 'Recargando cada 30s (hoy está dentro del rango).'
      : 'Sin recarga (hoy está fuera del rango).';

    if (ok) {
      // Recarga suave: conserva la URL actual
      location.reload();
    } else {
      // Si hoy ya no está en el rango, detén futuras recargas
      stop();
    }
  }

  function start(){
    stop();
    // Ejecuta una verificación inmediata (no recarga ahora, solo prepara estado visual)
    if ($hint) $hint.textContent = todayInRange()
      ? 'Recargando cada 30s (hoy está dentro del rango).'
      : 'Sin recarga (hoy está fuera del rango).';

    timer = setInterval(tick, INTERVAL_MS);
  }

  // Inicia y reprograma cuando cambian fechas
  start();
  ['change','input'].forEach(ev => {
    $desde?.addEventListener(ev, start);
    $hasta?.addEventListener(ev, start);
    $rango?.addEventListener(ev, start);
  });

  // Pausa cuando la pestaña no está visible; reanuda al volver
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop(); else start();
  });

  // Limpia al salir
  window.addEventListener('beforeunload', stop);
})();
</script>


@endsection
