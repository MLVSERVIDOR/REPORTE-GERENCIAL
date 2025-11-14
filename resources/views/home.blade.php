<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">


<!-- Mirrored from themesbrand.com/velzon/html/master/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 16 Aug 2024 17:40:28 GMT -->
<head>

    <meta charset="utf-8" />
    <title>Dashboard | Municipalidad Distrital de la Victoria</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Municipalidad Distrital de la Victoria" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo-sm.png">

    <!-- jsvectormap css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />

</head>

<body>
    @php
    /** @var array $usuario */
    $usuario = session('usuario') ?? [];
    @endphp

    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index-2.html" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-dark.png" alt="" height="17">
                        </span>
                    </a>

                    <a href="index-2.html" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-light.png" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                
            </div>

            <div class="d-flex align-items-center">

                

                

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    
                        <button type="submit" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <img class="rounded-circle header-profile-user" src="assets/images/users/user-dummy-img.jpg" alt="Header Avatar">
                                <span class="text-start ms-xl-2">
                                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $usuario['usuario'] ?? (auth()->user()->name ?? 'Invitado') }}</span><br>
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Cerrar sesión
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                    </form>
                                    <!--<span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Cerrar Sesiòn</span>-->
                                </span>
                            </span>
                        </button>
                    
                </div>
            </div>
        </div>
    </div>
</header>


        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="index-2.html" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-dark.png" alt="" height="17">
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="index-2.html" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-light.png" alt="" height="50">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>
            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                        <li class="nav-item">
                            <a href="{{ route('dashboards') }}" class="nav-link " >
                                <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                            </a> 
                        </li> <!-- end Dashboard Menu -->
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col">

                            <div class="h-100">
                                <div class="row mb-3 pb-1">
                                    <div class="col-12">
                                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                            <div class="flex-grow-1">
                                                <h4 class="fs-16 mb-1">Bienvenido,
                                                    <b>{{ $usuario['nombres'] ?? (auth()->user()->name ?? 'Usuario') }}
                                                    {{ $usuario['apellidos'] ?? '' }}</b>
                                                </h4>
                                                <p class="text-muted mb-0">{{ $usuario['area_nombre'] ?? $usuario['area_codigo'] ?? 'Sin área' }}</p>
                                            </div>
                                            <div class="mt-3 mt-lg-0">
                                                
                                            </div>
                                        </div><!-- end card header -->
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->

                                @yield('content')



                            </div> <!-- end .h-100-->

                        </div> <!-- end col -->

                        
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Municipalidad Distrital de la Victoria.
                        </div>
                        <div class="col-sm-6">
                            <!--<div class="text-sm-end d-none d-sm-block">
                                Design & Develop by Themesbrand
                            </div>-->
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    

   

    <!-- JAVASCRIPT -->
     
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector map-->
    <script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
    <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>


      <!-- echarts js -->
    <!-- echarts js -->
<script src="assets/libs/echarts/echarts.min.js"></script>
<style>
:root {
  --color-principal: #405189;
}

/* estilo normal */
.page-link {
  color: var(--color-principal);
  border: 1px solid #dee2e6;
  transition: all 0.2s ease-in-out;
}

.page-item.active .page-link {
  background-color: #405189 !important;
  border-color: #405189 !important;
  color: #fff !important;
  font-weight: 600;
}
/* efecto hover */
.page-link:hover {
  background-color: rgba(64, 81, 137, 0.1);
  border-color: var(--color-principal);
  color: var(--color-principal);
}

/* página activa */
/*.page-link.active-page {
  background-color: var(--color-principal) !important;
  color: #fff !important;
  border-color: var(--color-principal) !important;
  font-weight: 600;
}*/

/* desactivados (← y → cuando no hay más páginas) */
.page-item.disabled .page-link {
  color: #aaa !important;
  background-color: #f8f9fa;
  border-color: #dee2e6;
  pointer-events: none;
}
/* Animación y transición base */
.area-card {
  transition: all 0.25s ease;
  border: 1px solid #e9ecef;
}

/* Hover sutil para todos */
.area-card:hover {
  box-shadow: 0 0 10px rgba(64, 81, 137, 0.15);
}

/* Card activa (cuando se hace clic en Ver detalle) */
.area-card.active {
  border: 1px solid #2385ba !important;
  box-shadow: 0 0 14px rgba(64, 81, 137, 0.4);
  transform: scale(1.02);
  background-color: #dff0fa;
  position: relative;
}

/* Pequeño marcador visual arriba a la derecha */
.area-card.active::after {
  content: "✓";
  position: absolute;
  top: 8px;
  right: 10px;
  font-size: 16px;
  color: #2385ba;
  font-weight: bold;
}
</style>
<!-- 🔧 Parche ECharts: auto-resize sin romper animaciones -->


<script>
 window.__rowsRaw = @json($resultados ?? []);
  window.__totalGeneral = @json((float)($totalGeneral ?? 0));
  window.__areas = @json($areasPadre ?? []);
  window.__rowsDeps = @json(
    isset($rowsDeps)
      ? collect($rowsDeps)->map(function($r){
          return ['gerencia' => $r->dep_codigo, 'monto' => (float)$r->total_monto];
        })->values()
      : []
  );
  window.__rows = window.__rowsRaw;



  // Helper global para normalizar cod_partida (quita espacios)
  window.normPartida = window.normPartida || function (s) {
    s = String(s || '').trim();
    return s.replace(/\s+/g, '');
  };



// 🔹 Consultar el total general sin recargar
document.addEventListener('DOMContentLoaded', function () {
    const inputDesde = document.getElementById('desde');
    const inputHasta = document.getElementById('hasta');
    const totalSpan  = document.getElementById('totalGeneral');

    if (!inputDesde || !inputHasta || !totalSpan) {
        console.warn("⚠️ No se encontraron los elementos 'desde', 'hasta' o 'totalGeneral'.");
        return;
    }

    async function actualizarTotal() {
        const desde = inputDesde.value?.trim();
        const hasta = inputHasta.value?.trim();

        if (!desde || !hasta) return;

        try {
            const res = await fetch(`/api/total-general?desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`);
            const data = await res.json();

            if (data.success) {
                const totalFormateado = new Intl.NumberFormat('es-PE', {
                    style: 'currency',
                    currency: 'PEN',
                    minimumFractionDigits: 2
                }).format(data.total);

                totalSpan.textContent = totalFormateado;
                totalSpan.classList.add('text-success');
                setTimeout(() => totalSpan.classList.remove('text-success'), 800);
            } else {
                console.error('Error del servidor:', data.message);
                totalSpan.textContent = 'S/. 0.00';
            }
        } catch (err) {
            console.error('❌ Error al obtener total general:', err);
            totalSpan.textContent = 'S/. 0.00';
        }
    }

    // Escucha cambios
    inputDesde.addEventListener('change', actualizarTotal);
    inputHasta.addEventListener('change', actualizarTotal);

    // Llama una vez al iniciar
    actualizarTotal();
});








    var fechaDesde = "{{ $desde }}";
    var fechaHasta = "{{ $hasta }}";

    function formatSoles(valor) {
        const numero = Number(valor) || 0;
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(numero);
    }

document.addEventListener('click', function(e) {
    if (e.target.closest('.ver-detalle')) {
        const btn = e.target.closest('.ver-detalle');
        const codigoHijo = btn.dataset.codigo;
        cargarDetalleArea(codigoHijo, 1);
    }
});

function cargarDetalleArea(codigoHijo, pagina = 1) {
  const url = `/repo/detalle?codigo_hijo=${encodeURIComponent(codigoHijo)}&desde=${encodeURIComponent(fechaDesde)}&hasta=${encodeURIComponent(fechaHasta)}&page=${encodeURIComponent(pagina)}`;

  fetch(url, { cache: 'no-store' })
    .then(res => res.json())
    .then(json => {
      // Compatibilidad con paginator de Laravel
      const data = Array.isArray(json.data) ? json.data : [];

const currentPage = Number(
  (json.current_page != null) ? json.current_page :
  (json.page != null)         ? json.page :
                                pagina
);

const lastPage = Number(
  (json.last_page != null) ? json.last_page : 1
);

const total = Number(
  (json.total != null) ? json.total : data.length
);

const perPage = Number(
  (json.per_page != null) ? json.per_page : (data.length || 10)
);

const totalMonto = Number(
  (json.total_monto != null) ? json.total_monto : 0
);

      const nombreGrupo = (data.length > 0 && data[0].grupo) ? data[0].grupo : codigoHijo;

      const startRecord = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
      const endRecord   = total > 0 ? Math.min(currentPage * perPage, total) : 0;

      let html = `
        <div class="card">
          <div class="card-header align-items-center d-flex alert alert-success alert-dismissible alert-label-icon rounded-label fade show material-shadow" style="margin-bottom:0.5rem">
            <i class=" ri-home-2-line label-icon"></i> 
            <h4 class=" mb-0 flex-grow-1">Detalle del área</h4>
            <hr class="my-1">
          </div>

          <div class="card-body" style='padding-top: 0rem;'>
            <div class=" align-items-center d-flex" style='padding-bottom:1.5rem;'>
              <h4 class="card-title mb-0 flex-grow-1">${nombreGrupo}</h4>
              <div class="flex-shrink-0">
                <div class="alert alert-info material-shadow mb-0 p-2 text-center">
                  <span class="fw-semibold text-uppercase fs-16">Total: </span>
                  <span class="fs-16">${formatSoles(totalMonto)}</span>
                </div>
              </div>
            </div>
            


            <div class="table-responsive table-card">
              <table class="table table-hover table-centered align-middle mb-0">
                <tbody>
                  <tr style="background-color:#e2e5ed; color:#364574; text-align:center;">
                    <td><b>Concepto</b></td>
                    <td><b>Monto</b></td>
                  </tr>`;

                if (data.length === 0) {
                  html += `
                    <tr>
                      <td colspan="2" class="text-center text-muted py-4">Sin registros para este rango.</td>
                    </tr>`;
                } else {
                  data.forEach(r => {
                    html += `
                    <tr>
                      <td style="width:80%"><h5 class="fs-11 my-1 fw-normal">${r.concepto || '-'}</h5></td>
                      <td style="width:20%; text-align:center;"><h5 class="fs-14 my-1 fw-semibold">${formatSoles(Number(r.monto || 0))}</h5></td>
                    </tr>`;
                  });
                }

                html += `
                </tbody>
              </table>
            </div>

            <div class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
              <div class="col-sm">
                <div class="text-muted">
                  Mostrando <span class="fw-semibold">${startRecord}</span> -
                  <span class="fw-semibold">${endRecord}</span> de
                  <span class="fw-semibold">${total}</span> registros
                </div>
              </div>
              <div class="col-sm-auto mt-3 mt-sm-0">
                <ul class="pagination pagination-separated pagination-sm mb-0 justify-content-center">`;

      if (lastPage > 1) {
        let start = Math.max(1, currentPage - 2);
        let end   = Math.min(lastPage, currentPage + 2);
        if (currentPage <= 2) end = Math.min(5, lastPage);
        if (currentPage >= lastPage - 1) start = Math.max(1, lastPage - 4);

        for (let i = start; i <= end; i++) {
          const isActive = i === currentPage ? 'active' : '';
          html += `
            <li class="page-item ${isActive}">
              <a href="#" class="page-link detalle-pagina ${isActive}" data-page="${i}" data-codigo="${codigoHijo}">
                ${i}
              </a>
            </li>`;
        }
      }

      html += `
                </ul>
              </div>
            </div>
          </div>
        </div>`;

      const globalDiv = document.getElementById('detalle-global');
      if (globalDiv) {
        globalDiv.innerHTML = html;
        globalDiv.scrollIntoView({ behavior: 'smooth' });
      }

      console.log("Cargando detalle", { codigoHijo, currentPage, lastPage, total, perPage });
    })
    .catch(err => {
      console.error("Error cargando detalle:", err);
      const globalDiv = document.getElementById('detalle-global');
      if (globalDiv) {
        globalDiv.innerHTML = `
          <div class="alert alert-danger text-center">
            Error al cargar el detalle del área. Intente nuevamente.
          </div>`;
      }
    });
}

// Un SOLO listener global
document.addEventListener('click', function (e) {
  const btnDetalle = e.target.closest('.ver-detalle');
  if (btnDetalle) {
    e.preventDefault();
    const codigoHijo = btnDetalle.dataset.codigo;

    document.querySelectorAll('.area-card').forEach(card => card.classList.remove('active'));
    const cardSel = btnDetalle.closest('.area-card');
    if (cardSel) cardSel.classList.add('active');

    cargarDetalleArea(codigoHijo, 1);
    return;
  }

  if (e.target.matches('.detalle-pagina')) {
    e.preventDefault();
    const page = Number(e.target.dataset.page);
    const codigo = e.target.dataset.codigo;
    if (page && codigo) cargarDetalleArea(codigo, page);
  }
});

   
 
  // ===== DEBUG SEGURO =====
  (function(){
    const rows = Array.isArray(window.__rows) ? window.__rows : [];
    if (rows.length) {
      console.log('[rows sample]', rows[0]);
      console.log('[keys]', Object.keys(rows[0]));
      // intenta mostrar cod_partida en sus posibles variantes
      console.log('[cod_partida]', rows[0].cod_partida);
      console.log('[cod_partida_raw]', rows[0].cod_partida_raw);
      console.log('[cod_partida_norm]', rows[0].cod_partida_norm);
      // ejemplo de normalización
      const rawCode = rows[0].cod_partida_raw ?? rows[0].cod_partida ?? rows[0].partida ?? '';
      console.log('[cod_partida normalizado]', window.normPartida(rawCode));
    } else {
      console.warn('[DEBUG] __rows está vacío');
    }

    console.log('[areas sample]', (window.__areas || [])[0] || null);
    console.log('[rowsDeps sample]', (window.__rowsDeps || [])[0] || null);
  })();
(function () {
  if (!window.echarts) { console.warn('ECharts no está disponible.'); return; }

  const debounce=(f,d=100)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>f(...a),d)}};
  const raf=(fn)=>window.requestAnimationFrame?requestAnimationFrame(fn):setTimeout(fn,16);

  function waitForSize(el,{timeout=4000,interval=50}={}){
    return new Promise((res,rej)=>{
      const t0=performance.now();
      (function loop(){
        const w=el.clientWidth,h=el.clientHeight;
        if(w>0&&h>0) return res({w,h});
        if(performance.now()-t0>timeout) return rej(new Error('timeout: contenedor sin tamaño'));
        setTimeout(loop,interval);
      })();
    });
  }

  function attachObservers(inst,dom){
    if (inst.__autoResize) return;

    const state={ w:dom.clientWidth||0, h:dom.clientHeight||0, visible:true };
    const doResizeIfNeeded=()=>{
      if (inst.__suppressResize) return; // 🚫 no interrumpir la primera animación
      const w=dom.clientWidth||0, h=dom.clientHeight||0;
      if (!w||!h||!state.visible) return;
      if (w===state.w && h===state.h) return;
      state.w=w; state.h=h;
      try { inst.resize(); } catch(_) {}
    };
    const debouncedResize=debounce(()=>raf(doResizeIfNeeded), 80);

    // Observa el elemento y ancestros
    const ros=[]; let n=dom;
    while(n&&n.nodeType===1){
      try{ const ro=new ResizeObserver(debouncedResize); ro.observe(n); ros.push(ro); }catch(_){}
      if (n===document.documentElement) break;
      n=n.parentElement;
    }

    // Pausar/continuar cuando (no) visible
    let io=null;
    if ('IntersectionObserver' in window){
      io=new IntersectionObserver((entries)=>{
        const e=entries[0]; state.visible=!!(e&&e.isIntersecting);
        if (state.visible) debouncedResize();
      },{threshold:0});
      io.observe(dom);
    }

    // Cambios de clase/estilo (tabs/modales que muestran/ocultan)
    let mo=null;
    try{
      mo=new MutationObserver(debouncedResize);
      let a=dom;
      while(a&&a.nodeType===1){
        mo.observe(a,{attributes:true,attributeFilter:['class','style']});
        if (a===document.documentElement) break;
        a=a.parentElement;
      }
    }catch(_){}

    const refresh=debouncedResize;
    window.addEventListener('resize',refresh);
    window.addEventListener('orientationchange',refresh);
    window.addEventListener('pageshow',refresh);
    document.addEventListener('visibilitychange',()=>{ if (document.visibilityState==='visible') refresh(); });
    document.addEventListener('shown.bs.tab',refresh);
    document.addEventListener('shown.bs.modal',refresh);
    document.addEventListener('shown.bs.collapse',refresh);
    if (document.fonts?.ready) { document.fonts.ready.then(refresh).catch(()=>{}); }

    inst.__autoResize={
      cleanup(){
        ros.forEach(ro=>{try{ro.disconnect();}catch(_){}}); 
        if (io){try{io.disconnect();}catch(_){}} 
        if (mo){try{mo.disconnect();}catch(_){}} 
        window.removeEventListener('resize',refresh);
        window.removeEventListener('orientationchange',refresh);
        window.removeEventListener('pageshow',refresh);
        document.removeEventListener('visibilitychange',refresh);
        document.removeEventListener('shown.bs.tab',refresh);
        document.removeEventListener('shown.bs.modal',refresh);
        document.removeEventListener('shown.bs.collapse',refresh);
      }
    };
  }

  // Guardamos referencia al init original
  const _init = echarts.init;

  echarts.init = function(dom, theme, opts){
    // Crear instancia con useDirtyRect (performance) sin tocar animación
    const inst = _init.call(echarts, dom, theme, Object.assign({useDirtyRect:true}, opts));

    // ⏱️ SUAVIZADOR DE PRIMERA ANIMACIÓN:
    // En la primera llamada a setOption, suprimimos los resize hasta que termine la animación inicial (evento 'finished').
    const _setOption = inst.setOption.bind(inst);
    inst.__firstSetDone = false;
    inst.__suppressResize = false;
    inst.setOption = function(option, notMerge, lazyUpdate){
      if (!inst.__firstSetDone) {
        inst.__firstSetDone = true;
        inst.__suppressResize = true;           // 🚫 no hacer resize mientras arranca la animación
        const offFinished = ()=>{
          inst.__suppressResize = false;        // ✅ ya se puede redimensionar
          inst.off('finished', offFinished);
        };
        // cuando ECharts termina de renderizar/animar, volvemos a permitir resize
        inst.on('finished', offFinished);
      }
      return _setOption(option, notMerge, lazyUpdate);
    };

    // Solo forzamos un primer resize si el contenedor arrancó en 0x0 (evita cortar animación cuando ya hay tamaño)
    const cw = dom.clientWidth || 0, ch = dom.clientHeight || 0;
    if (cw===0 || ch===0) {
      waitForSize(dom).finally(()=>{ try{ if (!inst.__suppressResize) inst.resize(); }catch(_){ } });
    }

    // Observadores (elemento + ancestros)
    attachObservers(inst, dom);

    return inst;
  };

  // Limpieza en unload (opcional)
  window.addEventListener('unload', ()=>{
    (echarts.getInstances?.()||[]).forEach(inst=>{
      try { inst.__autoResize?.cleanup(); } catch(_) {}
      delete inst.__autoResize;
    });
  });
})();


 // Ajusta "rows" a la variable que uses: $rows, $resultados, etc.
  //window.__rows = @json($rows ?? $resultados ?? []);

document.addEventListener('DOMContentLoaded', function () {
  var el = document.querySelector('.counter-value[data-target]');
  if (!el) return;

  var raw = el.getAttribute('data-target');
  var target = Number(raw); // debe ser 1234.56
  if (!isFinite(target)) {
    console.warn('counter-value: data-target inválido =>', raw);
    return; // evita romper
  }

  // Si tienes un plugin que no acepta decimales, descomenta:
  // target = Math.round(target); // trabajarías en enteros

  // Si NO quieres animación, simplemente fija el texto:
  el.textContent = target.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  // Si quieres animación casera simple (y admite decimales):
  /*
  var start = 0, steps = 30, i = 0;
  var tick = function(){
    i++;
    var val = start + (target - start) * (i/steps);
    el.textContent = val.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (i < steps) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
  */
});
</script>
<script src="assets/js/reporteCategorias.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>


  


</body>


<!-- Mirrored from themesbrand.com/velzon/html/master/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 16 Aug 2024 17:40:28 GMT -->
</html>