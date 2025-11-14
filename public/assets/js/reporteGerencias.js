document.addEventListener('DOMContentLoaded', function () {
  function getChartColorsArray(t) {
    var el = document.getElementById(t);
    if (el !== null) {
      var key = "data-colors" + (("-" + (document.documentElement.getAttribute("data-theme") || "")) || "");
      var colors = el.getAttribute(key) || el.getAttribute("data-colors");
      if (colors) {
        try { colors = JSON.parse(colors); } catch(e){ colors = null; }
        if (!colors) return null;
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
      console.warn("data-colors attributes not found on", t);
    }
    return null;
  }

  const debounce = (fn, d=120) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };

  var posList, labelOption, chartDom, myChart, option;
  var chartBarLabelRotationColors = getChartColorsArray("chart-bar-label-rotation") || ["#405189","#0ab39c","#f7b84b","#f06548"];

  (function () {
    chartDom = document.getElementById("chart-bar-label-rotation");
    if (!chartDom || !window.echarts) return;

    myChart = echarts.init(chartDom);

    // 1) Áreas padre (desde backend)
    // window.__areas = [{ codigo_area: "0100", nombre_area: "GERENCIA X" }, ...]
    const areas = Array.isArray(window.__areas) ? window.__areas : [];
    let categorias = areas
      .map(a => (a.nombre_area || '').toString().trim())
      .filter(Boolean);

    // Mapa: codigo_area -> índice
    const indexByCodigo = {};
    areas.forEach((a, i) => {
      const cod = (a.codigo_area || '').toString().trim();
      if (cod) indexByCodigo[cod] = i;
    });

    // 2) Suma de montos por gerencia
    // window.__rows = [{ gerencia: "0100", monto: 123.45 }, ...]
    const rows = Array.isArray(window.__rows) ? window.__rows : [];
    const totals = new Array(categorias.length).fill(0);

    rows.forEach(r => {
      const cod = (r.gerencia || r.area || '').toString().trim();
      const idx = indexByCodigo[cod];
      if (idx !== undefined) {
        const v = parseFloat(r.monto ?? 0) || 0;
        totals[idx] += v;
      }
    });

    const dataSeries = totals.map(n => Number(n.toFixed(2)));

    if (categorias.length === 0) {
      categorias = ['Sin datos'];
      dataSeries.length = 0;
      dataSeries.push(0);
    }

    // 3) Labels (monto encima y horizontal)
    labelOption = {
      show: true,
      position: "top",
      rotate: 0,
      distance: 6,
      align: "center",
      verticalAlign: "bottom",
      formatter: function (p) {
        const v = Number(p.value || 0);
        return "S/. " + v.toLocaleString("es-PE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      },
      fontSize: 12
    };

    // ancho “virtual” por barra para que el nombre ocupe su ancho
    function computeLabelWidth() {
      const w = chartDom.clientWidth || 600;
      const padding = 32 + 32; // aprox. grid left/right
      const n = Math.max(1, categorias.length);
      const per = (w - padding) / n;
      return Math.max(60, Math.floor(per * 0.9));
    }

    function buildOption() {
      const axisLabelWidth = computeLabelWidth();

      return {
        grid: { left: "0%", right: "2%", bottom: "8%", top: "12%", containLabel: true },
        tooltip: { trigger: "axis", axisPointer: { type: "shadow" } },
        legend: { data: ["Total"], textStyle: { color: "#858d98" } },
        color: chartBarLabelRotationColors,
        toolbox: {
          show: true,
          orient: "vertical",
          left: "right",
          top: "center",
          feature: {
            mark: { show: true },
            dataView: { show: true, readOnly: false },
            magicType: { show: true, type: ["line", "bar", "stack"] },
            restore: { show: true },
            saveAsImage: { show: true },
          },
        },
        xAxis: [{
          type: "category",
          axisTick: { show: false },
          data: categorias,
          axisLine: { lineStyle: { color: "#858d98" } },
          axisLabel: {
            interval: 0,
            width: axisLabelWidth,
            overflow: 'break',   // permite saltos de línea
            lineHeight: 14,
            margin: 12,
            align: 'center'
          }
        }],
        yAxis: {
          type: "value",
          axisLine: { lineStyle: { color: "#858d98" } },
          splitLine: { lineStyle: { color: "rgba(133, 141, 152, 0.1)" } }
        },
        textStyle: { fontFamily: "Poppins, sans-serif" },
        series: [
          { name: "Total", type: "bar", barGap: 0, label: labelOption, emphasis: { focus: "series" }, data: dataSeries }
        ],
      };
    }

    option = buildOption();
    myChart.setOption(option);

    const onResize = debounce(() => {
      myChart.setOption(buildOption(), false, true);
      myChart.resize();
    }, 150);
    window.addEventListener('resize', onResize);
  })();

  // compat con tu template
  getChartColorsArray("chart-horizontal-bar");
});

