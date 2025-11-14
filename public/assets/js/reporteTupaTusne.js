// === Doughnut DINÁMICO (TUPA / TUSNE) con texto CENTRADO ===
var chartScatterColors =
  (
    (chartDom = document.getElementById("chart-doughnut")),
    chartDom &&
    (
      (myChart = echarts.init(chartDom)),

      (function () {
        // 1) Filas desde backend (inyecta en Blade: window.__rows = @json($rows))
        const rows = Array.isArray(window.__rows) ? window.__rows : [];

        // 2) Sumas por tipo_tra (TUPA=3, TUSNE=4)
        const tupa = rows
          .filter(r => String(r.tipo_tra ?? '') === '3')
          .reduce((acc, r) => acc + (parseFloat(r.monto ?? 0) || 0), 0);

        const tusne = rows
          .filter(r => String(r.tipo_tra ?? '') === '4')
          .reduce((acc, r) => acc + (parseFloat(r.monto ?? 0) || 0), 0);

        const cuis = rows
          .filter(r => String(r.tipo_tra ?? '') === '7')
          .reduce((acc, r) => acc + (parseFloat(r.monto ?? 0) || 0), 0);


        // 3) Data para ECharts
        const data = [
          { value: Number(tupa.toFixed(2))  || 0, name: "T.U.P.A." },
          { value: Number(tusne.toFixed(2)) || 0, name: "T.U.S.N.E." },
          { value: Number(cuis.toFixed(2)) || 0, name: "CUIS" },
        ];

        const total = data.reduce((s, d) => s + (d.value || 0), 0);

        // 4) Colores desde data-colors o fallback
        const doughnutColors =
          getChartColorsArray("chart-doughnut") ||
          ["#5470c6", "#91cc75", "#fac858", "#ee6666"];

        // 5) Formateador S/.
        const formatSoles = (v) =>
          "S/. " + (Number(v || 0)).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });

        // 6) Texto centrado helper (muestra nombre + monto)
        const centerText = (name, value) =>
          `${name}\n${formatSoles(value)}`;

        // 7) Opción del doughnut con GRAPHIC (texto centrado)
        option = {
          tooltip: {
            trigger: "item",
            formatter: function (p) {
              const val = formatSoles(p.value);
              const pct = (p.percent != null) ? (p.percent).toFixed(1) + "%" : "";
              return `${p.name}<br/>${val}<br/>${pct}`;
            }
          },
          legend: {
            top: "5%",
            orient: "vertical",
            left: "left",
            textStyle: { color: "#858d98" },
            formatter: function (name) {
              const item = data.find(d => d.name === name);
              return item ? `${name}  ${formatSoles(item.value)}` : name;
            }
          },
          color: doughnutColors,
          series: [
            {
              name: "Recaudación",
              type: "pie",
              radius: ["40%", "60%"],
              avoidLabelOverlap: false,
              label: {
                show: true,
                position: "outside",
                formatter: function (p) {
                  return `${p.name}\n${formatSoles(p.value)}\n${(p.percent || 0).toFixed(1)}%`;
                }
              },
              labelLine: { show: true },
              emphasis: { label: { show: true, fontSize: 16, fontWeight: "bold" } },
              data: data
            }
          ],
          // Texto centrado (total al cargar)
          graphic: {
            elements: [
              {
                type: "text",
                left: "center",
                top: "middle",
                z: 100,
                style: {
                  text: centerText("TOTAL", total),
                  textAlign: "center",
                  fill: "#555",
                  fontSize: 14,
                  fontFamily: "Poppins, sans-serif",
                  fontWeight: "bold",
                  lineHeight: 18
                }
              }
            ]
          },
          textStyle: { fontFamily: "Poppins, sans-serif" }
        };

        myChart.setOption(option);

        // 8) Interacción: al pasar el mouse, mostrar nombre+monto en el centro
        myChart.on("mouseover", function (p) {
          if (!p || p.componentType !== "series") return;
          const name = p.name || "";
          const val  = Number(p.value || 0);
          myChart.setOption({
            graphic: {
              elements: [
                {
                  type: "text",
                  left: "center",
                  top: "middle",
                  z: 100,
                  style: {
                    text: centerText(name, val),
                    textAlign: "center",
                    fill: "#333",
                    fontSize: 14,
                    fontFamily: "Poppins, sans-serif",
                    //fontWeight: "bold",
                    lineHeight: 18
                  }
                }
              ]
            }
          });
        });

        // 9) Al salir del gráfico, volver a mostrar TOTAL
        myChart.on("mouseout", function () {
          myChart.setOption({
            graphic: {
              elements: [
                {
                  type: "text",
                  left: "center",
                  top: "middle",
                  z: 100,
                  style: {
                    text: centerText("TOTAL", total),
                    textAlign: "center",
                    fill: "#555",
                    fontSize: 12,
                    fontFamily: "Poppins, sans-serif",
                    //fontWeight: "bold",
                    //lineHeight: 18
                  }
                }
              ]
            }
          });
        });
      })()
    ),

    // Continúa el encadenado como en tu archivo:
    getChartColorsArray("chart-bar-label-rotation")
  );