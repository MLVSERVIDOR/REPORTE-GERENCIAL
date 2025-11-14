<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FechaController extends Controller
{
    /**
     * 📊 Vista principal (Dashboard)
     */
    public function index(Request $request)
    {
        // Fechas predeterminadas
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        // 1) Datos base para tablas / otros gráficos (tu SP)
        $rows = DB::select(
            'EXEC Reporte.sp_BuscarFecha_2025 @desde = ?, @hasta = ?, @area = ?',
            [$desde->toDateString(), $hasta->toDateString(), null]
        );

        // 2) Áreas padre (para categorías del bar chart)
        $areasPadre = DB::table('Reporte.area')
            ->select(['codigo_area', 'nombre_area','siglas'])
            ->where('dependencia', '0000')
            ->orderBy('codigo_area')
            ->get();

        // 3) Totales por dependencia (padre) para el bar chart
        //    Usa tu SP que excluye tipo_tra=99 y respeta fechas
        $rowsDeps = DB::select(
            'EXEC Reporte.sp_TotalesPorDependencia @desde = ?, @hasta = ?',
            [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
        );
        // $rowsDeps esperado: dep_codigo, dep_nombre, total_monto

        // 4) Total general para la cabecera
        try {
            $totalGeneral = $this->obtenerTotalGeneral($desde, $hasta);
        } catch (\Throwable $e) {
            $totalGeneral = 0;
        }

        // 5) Renderiza la vista con TODO lo necesario
        return view('dashboards', [
            'resultados'    => $rows ?? [],
            'desde'         => $desde->toDateString(),
            'hasta'         => $hasta->toDateString(),
            'totalGeneral'  => $totalGeneral,
            'areasPadre'    => $areasPadre ?? [],
            'rowsDeps'      => $rowsDeps ?? [],
        ]);
    }

    /**
     * 🔹 Calcula el total general entre fechas (excluye tipo_tra=99)
     */

    private function obtenerTotalGeneral(Carbon $desde, Carbon $hasta): float
        {
            $row = DB::connection('sqlsrv')->selectOne(
                'EXEC Reporte.sp_BuscarFecha_2025_2_bak @desde = ?, @hasta = ?, @area = ?',
                [$desde->toDateString(), $hasta->toDateString(), null]
            );

            // si no hay fila o viene null, devolvemos 0.00
            $total = $row->total ?? 0;

            // casteo defensivo por si el SP devuelve money/numeric como string
            if (is_string($total)) {
                $total = (float) str_replace([',', ' '], '', $total);
            }

            return round((float)$total, 2);
        }





    /*private function obtenerTotalGeneral(Carbon $desde, Carbon $hasta): float
    {

         $res = DB::select(
            'EXEC Reporte.sp_BuscarFecha_2025_2 @desde = ?, @hasta = ?, @area = ?',
            [$desde->toDateString(), $hasta->toDateString(), null]
        );


        $res = DB::selectOne("
            SELECT
                SUM(
                    TRY_CONVERT(DECIMAL(18,2),
                        REPLACE(REPLACE(REPLACE(CAST(monto AS NVARCHAR(100)),'S/.',''),' ',''),',','')
                    )
                ) AS total
            FROM Caja.ReporteIncremental
            WHERE CAST(fecha AS date) BETWEEN ? AND ?
              AND (tipo_tra IS NULL OR TRY_CONVERT(INT, tipo_tra) <> 99)
        ", [$desde->toDateString(), $hasta->toDateString()]);

        return round((float)($res->total ?? 0), 2);
    }*/

    /**
     * 🔸 API: Total general en JSON (para AJAX del total)
     */
    public function apiTotalGeneral(Request $request)
    {
        try {
            $desde = $request->filled('desde')
                ? Carbon::parse($request->input('desde'))->startOfDay()
                : Carbon::now()->startOfMonth();

            $hasta = $request->filled('hasta')
                ? Carbon::parse($request->input('hasta'))->endOfDay()
                : Carbon::now()->endOfDay();

            $total = $this->obtenerTotalGeneral($desde, $hasta);

            return response()->json([
                'success' => true,
                'total'   => $total,
                'desde'   => $desde->toDateString(),
                'hasta'   => $hasta->toDateString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
