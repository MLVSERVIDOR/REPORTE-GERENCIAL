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

    var data = [
      { value: Number((tupa ).toFixed(2))  || 0, name: "T.U.P.A." },
      { value: Number((tusne).toFixed(2)) || 0, name: "T.U.S.N.E." }
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
  (function(){
  const id = "chart-bar-label-rotation";
  const el = document.getElementById(id);
  if (!el || !window.echarts) return;

  // --- Datos globales
  const areas = Array.isArray(window.__areas) ? window.__areas : [];
  const rowsDeps = Array.isArray(window.__rowsDeps) ? window.__rowsDeps : [];
  const rowsRaw  = Array.isArray(window.__rowsRaw)  ? window.__rowsRaw  : [];
  const baseRows = rowsDeps.length ? rowsDeps : rowsRaw;

  // --- Si no hay datos, mostrar mensaje
  if (!areas.length || !baseRows.length) {
    el.innerHTML = "<div style='text-align:center;padding:40px;color:#777;'>⚠️ No hay datos para mostrar.</div>";
    return;
  }

  // --- Construcción de categorías (nombres de áreas)
  //let categorias = areas.map(a => (a.nombre_area || '').trim()).filter(Boolean);
var isMobile = window.innerWidth < 768;

var categorias = areas.map(function(a) {
  var nombre = (a.nombre_area || '').toString().trim();
  var sigla = (a.siglas || '').toString().trim();

  // 🔹 Si es móvil y hay sigla, úsala
  if (isMobile && sigla) {
    return sigla;
  }

  // 🔹 Si no hay sigla o no es móvil, usar nombre completo
  return nombre;
}).filter(Boolean);
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
//console.table(totals);
  // --- Datos finales
  const dataSeries = totals.map(n => Number(n.toFixed(2)));
  if (!dataSeries.length) dataSeries.push(0);
  if (!categorias.length) categorias.push("Sin datos");

  //console.table(categorias.map((c,i)=>({ categoria:c, total:dataSeries[i] })));

  // --- Configuración de ECharts
  const barChart = echarts.init(el);

  function computeLabelWidth() {
    const w = el.clientWidth || 600;
    const n = Math.max(1, categorias.length);
    const padding = 64;
    const per = (w - padding) / n;
    return Math.max(60, Math.floor(per * 0.9));
  }

  function buildBarOption(){
    const isMobile = window.innerWidth < 768;
    const axisLabelWidth = computeLabelWidth();
var categorias = areas.map(a => isMobile && a.siglas ? a.siglas : a.nombre_area).filter(Boolean);
    var labelOptions = {
  show: true,
  position: "top",            // 🔹 Siempre arriba de la barra
  color: "#000",
  fontSize: isMobile ? 10 : 12,
  distance: isMobile ? 10 : 6, // 🔹 Más espacio arriba en móvil
  align: "center",
  verticalAlign: "bottom",
  formatter: function (p) {
    var n = Number(p.value || 0);

    // 🔹 Compacta el formato solo en móvil
    if (isMobile) {
      if (n >= 1_000_000) return "S/. " + (n / 1_000_000).toFixed(1) + "...";
      else if (n >= 1_000) return "S/. " + (n / 1_000).toFixed(1) + "...";
      else return "S/. " + n.toFixed(0);
    }

    // 🔹 En escritorio usa formato completo
    return formatSoles(n);
  }
};
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
          const sigla = (area.sigla || '').trim();
          const total = Number(params.value || 0);
          return `
            <div style="text-align:center; min-width:150px;">
              <b>${nombre}</b><br><br>
              <span  style="color:#ffd700; font-size:22px;">${formatSoles(total)}</span>
            </div>`;
        }
      },
      /*xAxis: [{
        type: "category",
        data: categorias,
        axisLabel: {
          interval: 0,
          width: axisLabelWidth,
          overflow: 'break',
          lineHeight: 14,
          margin: 12,
          align: 'center'
        }
      }],*/
      xAxis: [{
        type: "category",
        data: categorias,
        axisLabel: {
          interval: 0,
          width: axisLabelWidth,
          overflow: 'break',
          lineHeight: 12,                // un poco menor
          margin: 8,                     // menos margen
          align: 'center',
          fontSize: isMobile ? 9 : 10,   // ⬅️ reduce tamaño aquí
          fontWeight: 400                // opcional: más delgado
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
        label: labelOptions,
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

  function handleBarClick(nombreAreaEje, dataIndex, totalValue) {
  // 🔹 Normaliza texto (quita espacios extra y pasa a mayúsculas)
  function normalize(str) {
    return (str || '').toString().trim().toUpperCase().replace(/\s+/g, ' ');
  }

  const nombreEje = normalize(nombreAreaEje);

  // 🔹 Buscar coincidencia por nombre completo o sigla (ambos normalizados)
  const areaSeleccionada = areas.find(a => {
    const nombre = normalize(a.nombre_area);
    const sigla = normalize(a.sigla);
    return nombre === nombreEje || sigla === nombreEje;
  });

  // 🔹 Si no encuentra coincidencia, intenta búsqueda parcial (contiene)
  const areaFinal = areaSeleccionada || areas.find(a => {
    const nombre = normalize(a.nombre_area);
    const sigla = normalize(a.siglas);
    return nombre.includes(nombreEje) || sigla.includes(nombreEje);
  });

  if (!areaFinal) {
    console.warn("⚠️ No se encontró el área para:", nombreAreaEje);
    return;
  }

  const codigoPadre = (areaFinal.codigo_area || '').trim();
  if (!codigoPadre) {
    console.warn("⚠️ El área no tiene código válido:", areaFinal);
    return;
  }

  console.log("✅ Área clickeada:", areaFinal.nombre_area, "-", codigoPadre);

  // 🧹 Limpia el detalle global antes de recargar
  const detalleGlobal = document.getElementById('detalle-global');
  if (detalleGlobal) detalleGlobal.innerHTML = '';

  // 🔹 Llama al backend para obtener áreas hijas
  fetch(`/repo/hijos?codigo_padre=${codigoPadre}&desde=${fechaDesde}&hasta=${fechaHasta}`)
    .then(res => res.json())
    .then(data => {
      console.log("📦 Hijos recibidos:", data);
      let html = '';

      if (!data || !data.length) {
        html = `
          <div class="col-12 text-center text-muted py-4">
            <em>Sin áreas registradas.</em>
          </div>`;
      } else {
        data.forEach(row => {
          html += `
<div class="col-xl-3 col-md-6">
  <div class="card card-animate area-card" data-codigo="${row.codigo_hijo}">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1 overflow-hidden">
          <p class="text-uppercase fw-medium text-muted text-truncate mb-0">${row.nombre_hijo}</p>
        </div>
      </div>
      <div class="d-flex align-items-end justify-content-between mt-4">
        <div>
          <h4 class="fs-22 fw-semibold ff-secondary mb-4">
            <span class="counter-value">${formatSoles(row.total_monto)}</span>
          </h4>
          <button class="btn btn-sm btn-outline-primary ver-detalle" data-codigo="${row.codigo_hijo}">
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
    <div class="detalle-area" id="detalle-${row.codigo_hijo}" style="display:none;"></div>
  </div>
</div>`;
        });
      }

      const container = document.getElementById('dependencias-container');
      container.innerHTML = html;
      container.style.display = 'flex';
    })
    .catch(err => {
      console.error("❌ Error al cargar hijos:", err);
    });
}

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
    barChart.getZr().on("touchend", (params) => {
      if (Date.now() - lastTouch < 300) {
        const point = [params.offsetX, params.offsetY];
        if (barChart.containPixel("grid", point)) {
          const gridPoint = barChart.convertFromPixel({ seriesIndex: 0 }, point);
          const index = Math.round(gridPoint[0]);
          if (index >= 0 && index < categorias.length) {
            handleBarClick(categorias[index], index, dataSeries[index]);
          }
        }
      }
    });
  }

  attachBarClickEvents(barChart);

  window.addEventListener("orientationchange", function() {
  barChart.setOption(buildBarOption(), false, true);
  barChart.resize();
});

  // --- Redimensionamiento
  window.addEventListener('resize', debounce(()=>{
    barChart.setOption(buildBarOption(), false, true);
    barChart.resize();
  },150));
})();

});