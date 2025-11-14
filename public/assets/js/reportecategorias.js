document.addEventListener('DOMContentLoaded', function () {
  // ================= Helpers (una sola vez) =================
  function getChartColorsArray(t) {
    var el = document.getElementById(t);
    if (!el) return null;
    var key = "data-colors" + (("-" + (document.documentElement.getAttribute("data-theme") || "")) || "");
    var colors = el.getAttribute(key) || el.getAttribute("data-colors");
    if (!colors) return null;
    try { colors = JSON.parse(colors); } catch(e){ return null; }
    return colors.map(function (v) {
      var e = (v || "").toString().replace(" ", "");
      if (e.indexOf(",") === -1) {
        return getComputedStyle(document.documentElement).getPropertyValue(e) || e;
      } else {
        var p = v.split(",");
        return (p.length === 2)
          ? "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(p[0]) + "," + p[1] + ")"
          : e;
      }
    });
  }
  function debounce(fn, d){ var t; return function(){ clearTimeout(t); t=setTimeout(fn, d||120); }; }

  // === Utils ===
  function toNumber(n){
    if (typeof n === 'number') return n;
    // Sanitiza por si viene como "1,234.56" o con "S/ "
    var s = String(n ?? 0).replace(/[^\d.-]/g,'');
    var v = parseFloat(s);
    return isNaN(v) ? 0 : v;
  }
  function formatSoles(n){
    return 'S/. ' + (toNumber(n)).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});
  }

  // Normaliza partida (quita espacios)
  function normPartida(s){
    s = String(s||'').trim();
    return s.replace(/\s+/g,'');
  }

  // === Catálogo por CÓDIGO (ajusta a tus códigos reales) ===
  var PREDIAL_CODES   = ['1.1.21.11','1.1.21.12'];
  var ARBITRIOS_CODES = ['1.3.39.223','1.3.39.227','1.3.39.224'];
  var MULTAS_CODES    = ['1.5.21.11'];

  // === Construye data del pie desde rows ===
  function buildPieSeriesDataFromRows(rows){
    rows = Array.isArray(rows) ? rows : [];

    var predial=0, arbitrios=0, multas=0;

    // 1) Intento por CÓDIGO (si existen cod_partida_norm o similares)
    for (var i=0;i<rows.length;i++){
      var r = rows[i];
      // Preferimos lo que viene del back (cod_partida_norm), si no, armamos
      var code = r.partida_norm
  || (String(r.partida_raw || r.partida || r.partida || '')
        .trim()
        .replace(/\s+/g, '')); // <- normalización inline

      var monto = (r.monto != null) ? toNumber(r.monto) : toNumber(r.total_1);

      if (!code) continue;

      if (PREDIAL_CODES.indexOf(code)   !== -1){ predial += monto; continue; }
      if (ARBITRIOS_CODES.indexOf(code) !== -1){ arbitrios += monto; continue; }
      if (MULTAS_CODES.indexOf(code)    !== -1){ multas   += monto; continue; }
    }

    // 2) Si TODO quedó en 0 (no había códigos o no matchearon), FALLBACK por texto
    if (predial===0 && arbitrios===0 && multas===0){
      for (var j=0;j<rows.length;j++){
        var r2 = rows[j];
        var txt = String(r2.grupo || r2.concepto || r2.tipo || '').toUpperCase();
        var monto2 = (r2.monto != null) ? toNumber(r2.monto) : toNumber(r2.total_1);

        if (txt.indexOf('PREDIAL')   !== -1) { predial   += monto2; continue; }
        if (txt.indexOf('ARBITRIO')  !== -1) { arbitrios += monto2; continue; }
        if (txt.indexOf('MULTA')     !== -1) { multas    += monto2; continue; }
      }
    }

    // 3) Redondeo
    predial   = Math.round((predial   + Number.EPSILON)*100)/100;
    arbitrios = Math.round((arbitrios + Number.EPSILON)*100)/100;
    multas    = Math.round((multas    + Number.EPSILON)*100)/100;

    return [
      { name:'Predial',            value: predial },
      { name:'Arbitrios',          value: arbitrios },
      { name:'Multas Tributarias', value: multas }
    ];
  }

  // ================== 1) PIE: Predial / Arbitrios / Multas ==================
  (function(){
    var id = "chart-pie";
    var el = document.getElementById(id);
    if (!el || !window.echarts) return;

    // Asegúrate en Blade: window.__rows = @json($rows ?? []);
    var rows = Array.isArray(window.__rows) ? window.__rows : [];

    var data = buildPieSeriesDataFromRows(rows);
    // console.log('[DEBUG pie] data:', data, 'rows sample:', rows[0]);

    var colors = (typeof getChartColorsArray === 'function' && getChartColorsArray(id)) || ['#5b8ff9','#61d9a3','#ff9c6e'];
    var pieChart = echarts.init(el);
    pieChart.setOption({
      tooltip: {
        trigger: "item",
        formatter: function(p){
          var val = formatSoles(p.value || 0);
          var pct = (p.percent != null) ? p.percent.toFixed(1)+'%' : '';
          return p.name + '<br/>' + val + '<br/>' + pct;
        }
      },
      legend: {
        orient: "horizontal",
        left: "center",
        textStyle: { color: "#585e66" },
        formatter: function(name){
          var item = data.find(function(d){ return d.name === name; });
          return item ? (name) : name;
          //return item ? (name + ' ' + formatSoles(item.value)) : name;
        }
      },
      color: colors,
      series: [{
        name: "Recaudación",
        type: "pie",
        radius: "55%",
        data: data,
        emphasis: { itemStyle: { shadowBlur:10, shadowOffsetX:0, shadowColor:"rgba(0,0,0,0.5)" } },
        label: {
          show: true,
          formatter: function(p){
            var val = formatSoles(p.value || 0);
            var pct = (p.percent != null) ? p.percent.toFixed(1)+'%' : '';
            return p.name + '\n' + val + '\n' + pct;
          }
        }
      }],
      textStyle: { fontFamily: "Poppins, sans-serif" }
    });
  })();

  // ================== 2) DOUGHNUT: TUPA / TUSNE ==================
  (function(){
    var id = "chart-doughnut";
    var el = document.getElementById(id);
    if (!el || !window.echarts) return;

    var rows = Array.isArray(window.__rows) ? window.__rows : [];
    var tupa = rows.filter(function(r){ return String(r.tipo_tra ?? '') === '3'; })
                   .reduce(function(acc, r){ return acc + toNumber(r.monto); }, 0);
    var tusne = rows.filter(function(r){ return String(r.tipo_tra ?? '') === '4'; })
                    .reduce(function(acc, r){ return acc + toNumber(r.monto); }, 0);
    var cuis = rows.filter(function(r){ return String(r.tipo_tra ?? '') === '7'; })
                    .reduce(function(acc, r){ return acc + toNumber(r.monto); }, 0);
    var otros = rows.filter(function(r){ return String(r.tipo_tra ?? '') === '0'; })
                    .reduce(function(acc, r){ return acc + toNumber(r.monto); }, 0);

    var data = [
      { value: Number((tupa ).toFixed(2))  || 0, name: "T.U.P.A." },
      { value: Number((tusne).toFixed(2)) || 0, name: "T.U.S.N.E." },
      { value: Number((cuis).toFixed(2)) || 0, name: "C.U.I.S." },
      { value: Number((otros).toFixed(2)) || 0, name: "OTROS CONCEPTOS" }
    ];
    var total = data.reduce(function(s,d){ return s + (d.value || 0); }, 0);

    var colors = getChartColorsArray(id) || ["#5470c6", "#91cc75", "#fac858", "#ee6666"];
    var doughnutChart = echarts.init(el);
    var doughnutOption = {
      tooltip: {
        trigger: "item",
        formatter: function (p) {
          var val = formatSoles(p.value);
          var pct = (p.percent != null) ? p.percent.toFixed(1) + "%" : "";
          return p.name + "<br/>" + val + "<br/>" + pct;
        }
      },
      legend: {
        top: "0%",
        orient: "horizontal",
        left: "center",
        textStyle: { color: "#858d98" },
        formatter: function (name) {
          var item = data.find(function(d){ return d.name === name; });
          return item ? (name) : name;
          //return item ? (name + "  " + formatSoles(item.value)) : name;
        }
      },
      color: colors,
      series: [{
        name: "Recaudación",
        type: "pie",
        radius: ["40%", "60%"],
        avoidLabelOverlap: false,
        label: {
          show: true,
          position: "outside",
          formatter: function (p) {
            return p.name + "\n" + formatSoles(p.value) + "\n" + ((p.percent || 0).toFixed(1)) + "%";
          }
        },
        labelLine: { show: true },
        emphasis: { label: { show: true, fontSize: 16, fontWeight: "bold" } },
        data: data
      }],
      graphic: {
        elements: [{
          type: "text",
          left: "center",
          top: "middle",
          z: 100,
          style: {
            text: "TOTAL\n" + formatSoles(total),
            textAlign: "center",
            fill: "#555",
            fontSize: 14,
            fontFamily: "Poppins, sans-serif",
            fontWeight: "bold",
            lineHeight: 18
          }
        }]
      },
      textStyle: { fontFamily: "Poppins, sans-serif" }
    };
    doughnutChart.setOption(doughnutOption);

    // hover: mostrar segmento en el centro
    doughnutChart.on("mouseover", function (p) {
      if (!p || p.componentType !== "series") return;
      var txt = p.name + "\n" + formatSoles(Number(p.value || 0));
      doughnutChart.setOption({
        graphic: { elements: [{ type: "text", left: "center", top: "middle", z: 100,
          style: { text: txt, textAlign: "center", fill: "#333", fontSize: 14, fontFamily: "Poppins, sans-serif", lineHeight: 18 }
        }] }
      });
    });
    doughnutChart.on("mouseout", function () {
      doughnutChart.setOption({
        graphic: { elements: [{ type: "text", left: "center", top: "middle", z: 100,
          style: { text: "TOTAL\n" + formatSoles(total), textAlign: "center", fill: "#555", fontSize: 14, fontFamily: "Poppins, sans-serif", fontWeight:"bold", lineHeight: 18 }
        }] }
      });
    });
    window.addEventListener('resize', debounce(function(){ doughnutChart.resize(); },150));
  })();




  // ================== 3) BARRAS POR ÁREA PADRE ==================
  // ================== 3) BARRAS POR ÁREA PADRE ==================
(function(){
  const id = "chart-bar-label-rotation";
  const el = document.getElementById(id);
  if (!el || !window.echarts) return;

  // --- Datos globales
  const areas = Array.isArray(window.__areas) ? window.__areas : [];
  const rowsDeps = Array.isArray(window.__rowsDeps) ? window.__rowsDeps : [];
  const rowsRaw  = Array.isArray(window.__rowsRaw)  ? window.__rowsRaw  : [];
  const baseRows = rowsDeps.length ? rowsDeps : rowsRaw;

  if (!areas.length || !baseRows.length) {
    el.innerHTML = "<div style='text-align:center;padding:40px;color:#777;'>⚠️ No hay datos para mostrar.</div>";
    return;
  }

  // Helpers locales
  const esc  = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const norm = s => String(s ?? '').trim().toUpperCase().replace(/\s+/g, ' ');

  // --- Construcción de categorías (usa sigla en móvil si existe)
  const isMobileInit = window.innerWidth < 768;
  let categorias = areas.map(a => (isMobileInit && (a.sigla || a.siglas) ? (a.sigla || a.siglas) : a.nombre_area)).filter(Boolean);
  let currentCategorias = categorias.slice();

  // --- Mapa: codigo_area → índice
  const indexByCodigo = {};
  areas.forEach((a,i)=>{
    const cod = (a.codigo_area || '').trim();
    if (cod) indexByCodigo[cod] = i;
  });

  // --- Sumar montos por área
  const totals = new Array(categorias.length).fill(0);
  baseRows.forEach(r=>{
    const cod = (r.gerencia || r.area || '').toString().trim();
    const idx = indexByCodigo[cod];
    if (idx !== undefined) totals[idx] += Number(r.monto) || 0;
  });

  const dataSeries = totals.map(n => Number(n.toFixed(2)));
  if (!dataSeries.length) dataSeries.push(0);
  if (!categorias.length) categorias.push("Sin datos");

  const barChart = echarts.init(el);

  function computeLabelWidth(nCats) {
    const w = el.clientWidth || 600;
    const n = Math.max(1, nCats);
    const padding = 64;
    const per = (w - padding) / n;
    return Math.max(60, Math.floor(per * 0.9));
  }

  function buildBarOption(){
    const isMobile = window.innerWidth < 768;
    const cats = areas.map(a => (isMobile && (a.sigla || a.siglas) ? (a.sigla || a.siglas) : a.nombre_area)).filter(Boolean);
    currentCategorias = cats.slice();
    const axisLabelWidth = computeLabelWidth(cats.length);

    return {
      grid: { left: "0%", right: "2%", bottom: "8%", top: "12%", containLabel: true },
      tooltip: {
        trigger: "item",
        confine: true,
        transitionDuration: 0.3,
        backgroundColor: "rgba(50,50,50,0.85)",
        borderRadius: 6,
        padding: [8, 10],
        textStyle: { color: "#fff", fontSize: 13 },
        position: function (point, params, dom, rect, size) {
          const chartWidth = size.viewSize[0];
          const tooltipWidth = size.contentSize[0];
          const x = (chartWidth - tooltipWidth) / 2;
          let y = rect.y - size.contentSize[1] - 10;
          if (y < 0) y = rect.y + rect.height + 10;
          return [x, y];
        },
        formatter: function (params) {
          const index = params.dataIndex;
          const area = areas[index] || {};
          const nombre = (area.nombre_area || params.name || '').trim();
          const total = Number(params.value || 0);
          return `
            <div style="text-align:center; min-width:150px;">
              <b>${esc(nombre)}</b><br><br>
              <span style="color:#ffd700; font-size:22px;">${formatSoles(total)}</span>
            </div>`;
        }
      },
      xAxis: [{
        type: "category",
        data: cats,
        axisLabel: {
          interval: 0,
          width: axisLabelWidth,
          overflow: 'break',
          lineHeight: 12,
          margin: 8,
          align: 'center',
          fontSize: isMobile ? 9 : 10,
          fontWeight: 400
        }
      }],
      yAxis: {
        type: "value",
        axisLine: { lineStyle: { color: "#ccc" } },
        splitLine: { lineStyle: { color: "rgba(133,141,152,0.1)" } }
      },
      series: [{
        type: "bar",
        barGap: 0,
        label: {
          show: true,
          position: "top",
          color: "#000",
          fontSize: isMobile ? 10 : 12,
          distance: isMobile ? 10 : 6,
          align: "center",
          verticalAlign: "bottom",
          formatter: function (p) {
            var n = Number(p.value || 0);
            if (isMobile) {
              if (n >= 1_000_000) return "S/. " + (n / 1_000_000).toFixed(1) + "...";
              else if (n >= 1_000) return "S/. " + (n / 1_000).toFixed(1) + "...";
              else return "S/. " + n.toFixed(0);
            }
            return formatSoles(n);
          }
        },
        itemStyle: {
          color: new echarts.graphic.LinearGradient(0,0,0,1,[
            { offset: 0, color: "#78c800" },
            { offset: 1, color: "#00a9e5" }
          ])
        },
        emphasis: {
          focus: 'self',
          itemStyle: {
            opacity: 1,
            shadowBlur: 25,
            shadowColor: 'rgba(0,0,0,0.4)',
            borderWidth: 2,
            borderColor: '#000'
          }
        },
        blur: { itemStyle: { opacity: 0.2 } },
        data: dataSeries
      }]
    };
  }

  // --- Cargar gráfico
  barChart.setOption(buildBarOption());

  // ================== CLICK: encabezado + hijos (sin borrar encabezado) ==================
  function handleBarClick(nombreAreaEje, dataIndex, totalValue) {
    // 1) Resolver el área (preferir dataIndex; fallback por nombre/sigla)
    let areaFinal = (Array.isArray(areas) && areas[dataIndex]) ? areas[dataIndex] : null;
    if (!areaFinal) {
      const needle = norm(nombreAreaEje);
      areaFinal =
        areas.find(a => norm(a.nombre_area) === needle || norm(a.sigla || a.siglas) === needle) ||
        areas.find(a => norm(a.nombre_area).includes(needle) || norm(a.sigla || a.siglas).includes(needle));
    }
    if (!areaFinal) {
      console.warn("⚠️ No se encontró el área para:", nombreAreaEje);
      return;
    }

    const codigoPadre = (areaFinal.codigo_area || '').toString().trim();
    if (!codigoPadre) {
      console.warn("⚠️ El área no tiene código válido:", areaFinal);
      return;
    }

    // 2) Contenedor destino (acepta id correcto y un id antiguo con typo)
    const container =
      document.getElementById('dependencias-container') ||
      document.getElementById('ependencias-container');
    if (!container) {
      console.warn("⚠️ No existe #dependencias-container/#ependencias-container");
      return;
    }

    // Limpia detalle global si existe
    const detalleGlobal = document.getElementById('detalle-global');
    if (detalleGlobal) detalleGlobal.innerHTML = '';

    // 3) Pintar encabezado + holder para hijos
    const nombrePadreLocal = areaFinal.nombre_area || '';
    container.innerHTML = `
<div class="col-12 card  alert alert-success alert-dismissible alert-label-icon rounded-label fade show material-shadow">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <i class=" ri-home-2-line label-icon"></i>
    <h5 class="mb-0 ff-secondary">${esc(nombrePadreLocal)}</h5>
  </div>
  <hr class="my-1">
</div>
<div id="dependencias-hijas" class="row w-100 "></div>`;
    const hijosHolder = document.getElementById('dependencias-hijas');

    console.log("✅ Área clickeada:", nombrePadreLocal, "-", codigoPadre);

    // 4) Traer hijos y render SOLO en el holder
    const fDesde = (typeof window.fechaDesde !== 'undefined') ? window.fechaDesde : '';
    const fHasta = (typeof window.fechaHasta !== 'undefined') ? window.fechaHasta : '';
    const url = `/repo/hijos?codigo_padre=${encodeURIComponent(codigoPadre)}&desde=${encodeURIComponent(fDesde)}&hasta=${encodeURIComponent(fHasta)}`;

    fetch(url)
      .then(res => res.json())
      .then(json => {
        console.log("📦 Hijos recibidos:", json);

        // Acepta array directo o {data: [...]}
        const hijos = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);

        // Si el backend trae nombre oficial del padre, actualizar SOLO el header
        const nombrePadreApi =
          json?.nombre_padre ??
          json?.area_padre?.nombre ??
          hijos?.[0]?.nombre_padre ??
          hijos?.[0]?.area_padre ??
          null;

        if (nombrePadreApi) {
          if (container.firstElementChild) container.firstElementChild.remove();
          container.insertAdjacentHTML('afterbegin', `
            <div class="col-12 card card-animate alert alert-info  material-shadow">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h5 class="mb-0 text-uppercase fw-semibold text-muted">Área</h5>
    <h5 class="mb-0 ff-secondary">${esc(String(nombrePadreApi))}</h5>
  </div>
  <hr class="my-3">
</div>`);
        }

        if (!hijos.length) {
          hijosHolder.innerHTML = `
<div class="col-12 text-center text-muted py-4">
  <em>Sin áreas registradas.</em>
</div>`;
          return;
        }

        let cards = '';
        hijos.forEach(row => {
          const codigoHijo  = row.codigo_hijo ?? row.codigo ?? row.id ?? '';
          const nombreHijo  = row.nombre_hijo ?? row.nombre ?? row.descripcion ?? row.area ?? row.gerencia ?? '';
          const totalMonto  = row.total_monto ?? row.monto ?? row.total ?? row.importe ?? 0;

          cards += `
<div class="col-xl-3 col-md-6">
  <div class="card card-animate area-card" data-codigo="${esc(String(codigoHijo))}">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1 overflow-hidden">
          <p class="text-uppercase fw-medium text-muted text-truncate mb-0">${esc(String(nombreHijo))}</p>
        </div>
      </div>
      <div class="d-flex align-items-end justify-content-between mt-4">
        <div>
          <h4 class="fs-22 fw-semibold ff-secondary mb-4">
            <span class="counter-value">${formatSoles(totalMonto)}</span>
          </h4>
          <button class="btn btn-sm btn-outline-primary ver-detalle" data-codigo="${esc(String(codigoHijo))}">
            <i class="bx bx-search"></i> Detalle
          </button>
        </div>
        <div class="avatar-sm flex-shrink-0">
          <span class="avatar-title bg-info-subtle rounded fs-3">
            <i class="bx bx-network-chart text-info"></i>
          </span>
        </div>
      </div>
    </div>
    <div class="detalle-area" id="detalle-${esc(String(codigoHijo))}" style="display:none;"></div>
  </div>
</div>`;
        });

        hijosHolder.innerHTML = cards;
        container.style.display = 'flex';
      })
      .catch(err => {
        console.error("❌ Error al cargar hijos:", err);
        hijosHolder.innerHTML = `
<div class="col-12 text-center text-danger py-4">
  <em>Error al cargar áreas .</em>
</div>`;
      });
  }

  // Eventos click/touch
  function attachBarClickEvents(barChart) {
    // Desktop
    barChart.on("click", function (params) {
      if (params.componentType === "series" && params.seriesType === "bar") {
        handleBarClick(params.name, params.dataIndex, params.value);
      }
    });

    // Touch móvil
    let lastTouch = 0;
    barChart.getZr().on("touchstart", () => { lastTouch = Date.now(); });
    barChart.getZr().on("touchend", (zrEvt) => {
      if (Date.now() - lastTouch > 300) return;

      const point = [zrEvt.offsetX, zrEvt.offsetY];
      if (!barChart.containPixel("grid", point)) return;

      const gridPoint = barChart.convertFromPixel({ seriesIndex: 0 }, point);
      let index = Math.round(gridPoint[0]);

      if (!Number.isFinite(index) || index < 0) index = 0;
      const max = (Array.isArray(areas) ? areas.length : 0) - 1;
      if (index > max) index = max;

      const opt = barChart.getOption();
      const cats = (opt && opt.xAxis && opt.xAxis[0] && opt.xAxis[0].data) ? opt.xAxis[0].data : [];
      const name = cats[index] || '';
      const seriesData = (opt && opt.series && opt.series[0] && opt.series[0].data) ? opt.series[0].data : [];
      const value = Number(seriesData[index] || 0);

      handleBarClick(name, index, value);
    });
  }

  attachBarClickEvents(barChart);

  // Redimensionamiento / orientación
  window.addEventListener("orientationchange", function() {
    barChart.setOption(buildBarOption(), false, true);
    barChart.resize();
  });
  window.addEventListener('resize', debounce(()=>{
    barChart.setOption(buildBarOption(), false, true);
    barChart.resize();
  },150));
})();


});