<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RepoGerenciaController extends Controller
{
    public function index(Request $request)
{
    $request->validate([
        'desde' => ['nullable', 'date'],
        'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
    ]);

    $desde = $request->filled('desde')
        ? Carbon::parse($request->input('desde'))->startOfDay()
        : Carbon::now()->startOfMonth();

    $hasta = $request->filled('hasta')
        ? Carbon::parse($request->input('hasta'))->endOfDay()
        : Carbon::now()->endOfDay();

    // Áreas padre con sigla incluida
    $areasPadre = DB::table('Reporte.area')
        ->select('codigo_area', 'nombre_area', 'siglas')
        ->where('dependencia', '0000')
        ->orderBy('codigo_area')
        ->get();
    ///dd($areasPadre);
    // Filas base (filtro de fechas y exclusión de tipo_tra=99)
    $rows = DB::table('Caja.ReporteIncremental')
    ->select('gerencia', 'grupo', 'monto', 'tipo_tra', 'partida', 'fecha')
    ->whereBetween('fecha', [$desde, $hasta])
    ->where(function ($q) {
        // robusto ante strings como '99', ' 99'
        $q->whereNull('tipo_tra')
          ->orWhereRaw('TRY_CONVERT(INT, tipo_tra) <> 99');
    })
    ->get();

    // Totales por dependencia (SP)
   /* $rowsDeps = DB::select(
        'EXEC Reporte.sp_TotalesPorDependencia @desde = ?, @hasta = ?',
        [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
    );*/
    // Ejecuta el SP
$rowsDeps = DB::select(
    'EXEC Reporte.sp_TotalesPorDependencia @d = ?, @h = ?',
    [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
);

  // Trae meta de padres (para siglas y nombres “limpios”)
$padresMeta = DB::table('Reporte.area')
    ->selectRaw("RTRIM(LTRIM(codigo_area)) AS codigo_area,
                 RTRIM(LTRIM(nombre_area)) AS nombre_area,
                 RTRIM(LTRIM(siglas))       AS siglas")
    ->where('dependencia', '0000')
    ->get()
    ->keyBy('codigo_area');

// Normaliza rowsDeps: códigos/nombres y agrega siglas
$rowsDeps = collect($rowsDeps)->map(function($r) use ($padresMeta) {
    // Normaliza código y nombre del SP
    $code = trim($r->dep_codigo ?? $r->codigo_area ?? '');
    $name = trim($r->dep_nombre ?? $r->nombre_area ?? '');

    // Busca meta del padre para forzar nombre/siglas oficiales
    $meta = $padresMeta->get($code);
    if ($meta) {
        $r->dep_nombre = $meta->nombre_area ?: $name;
        $r->siglas     = $meta->siglas ?: null;
    } else {
        $r->dep_nombre = $name;
        $r->siglas     = null;
    }

    // Normaliza código
    $r->dep_codigo = $code;

    // Limpia/convierte total_monto a float
    $r->total_monto = (float) preg_replace('/[^\d\.\-]/', '', (string)($r->total_monto ?? 0));

    return $r;
})->values()->all();

// (opcional) log
\Log::info('rowsDeps_norm', array_slice($rowsDeps, 0, 5));

$categoriasDeps = collect($rowsDeps)->pluck('dep_nombre')->values()->all();
$montosDeps     = collect($rowsDeps)->pluck('total_monto')->map(fn($v)=>(float)$v)->values()->all();

    return view('dashboards', [
        'areasPadre'      => $areasPadre,
        'rows'            => $rows,
        'desde'           => $desde->toDateString(),
        'hasta'           => $hasta->toDateString(),
        'rowsDeps'        => $rowsDeps,
        'categoriasDeps'  => $categoriasDeps,
        'montosDeps'      => $montosDeps,
        'totalMonto'      => $totalMonto ?? 0,
    ]);
}

    /*public function hijos(Request $request)
        {
            $request->validate([
                'codigo_padre' => ['required','string'],
                'desde' => ['nullable','date'],
                'hasta' => ['nullable','date','after_or_equal:desde'],
            ]);

            $codigoPadre = $request->input('codigo_padre');

            $desde = $request->filled('desde')
                ? Carbon::parse($request->input('desde'))->startOfDay()
                : Carbon::now()->startOfMonth();

            $hasta = $request->filled('hasta')
                ? Carbon::parse($request->input('hasta'))->endOfDay()
                : Carbon::now()->endOfDay();

            $rowsHijos = DB::table('Reporte.area as p')
                ->join('Reporte.area as h', 'h.dependencia', '=', 'p.codigo_area')
                ->leftJoin('Caja.ReporteIncremental as r', function ($join) use ($desde, $hasta) {
                    $join->on('r.gerencia', '=', 'h.codigo_area')
                        ->whereBetween(DB::raw('CAST(r.fecha as date)'), [$desde->format('Y-m-d'), $hasta->format('Y-m-d')])
                        ->where(function($q){
                            $q->whereNull('r.tipo_tra')->orWhereRaw('TRY_CONVERT(INT, r.tipo_tra) <> 99');
                        });
                })
                ->select(
                    'h.codigo_area as codigo_hijo',
                    'h.nombre_area as nombre_hijo',
                    DB::raw("ISNULL(SUM(TRY_CONVERT(DECIMAL(18,2),
                        REPLACE(REPLACE(REPLACE(CAST(r.monto AS NVARCHAR(100)),'S/.',''),' ',''),',','')
                    )),0) as total_monto")
                )
                ->where('p.codigo_area', $codigoPadre)
                ->groupBy('h.codigo_area','h.nombre_area')
                ->orderByDesc('total_monto')
                ->get();

            return response()->json($rowsHijos);
        }*/

    public function hijos(Request $request)
    {
        $request->validate([
            'codigo_padre' => ['required', 'string'],
            'desde'        => ['nullable', 'date'],
            'hasta'        => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $codigoPadre = $request->input('codigo_padre');
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        // Ejecuta el SP con @accion = 1 (Reporte de áreas hijas)
        $rowsHijos = DB::select(
            'EXEC Reporte.sp_ObtenerReporteHijosPorArea @accion = ?, @codigoPadre = ?, @desde = ?, @hasta = ?',
            [1, $codigoPadre, $desde, $hasta]
        );

        // Normaliza y convierte total_monto a float
        $rowsHijos = collect($rowsHijos)->map(function($r) {
            $r->total_monto = (float) $r->total_monto;
            return $r;
        })->values()->all();

        return response()->json($rowsHijos);
    }

    public function detalleArea(Request $request)
    {
        $request->validate([
            'codigo_hijo' => 'required|string',
            'desde'       => 'required|date',
            'hasta'       => 'required|date',
            'page'        => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:1|max:200',
        ]);

        $codigoHijo = trim($request->input('codigo_hijo'));
        $desde      = Carbon::parse($request->input('desde'))->format('Y-m-d');
        $hasta      = Carbon::parse($request->input('hasta'))->format('Y-m-d');
        $page       = max(1, (int) $request->input('page', 1));
        $perPage    = (int) $request->input('per_page', 10);

        // Ejecuta el SP con @accion = 2 (Detalle de área específica)
        $result = DB::select(
            'EXEC Reporte.sp_ObtenerReporteHijosPorArea @accion = ?, @codigoHijo = ?, @desde = ?, @hasta = ?, @page = ?, @perPage = ?',
            [2, $codigoHijo, $desde, $hasta, $page, $perPage]
        );

        // El SP ya devuelve todos los datos necesarios en cada fila
        // Extraemos los metadatos de la primera fila (si existe)
        $data = [];
        $currentPage = $page;
        $lastPage = 1;
        $total = 0;
        $totalMonto = 0;

        if (!empty($result)) {
            $firstRow = $result[0];
            $currentPage = $firstRow->current_page ?? $page;
            $lastPage = $firstRow->last_page ?? 1;
            $perPage = $firstRow->per_page ?? $perPage;
            $total = $firstRow->total ?? 0;
            $totalMonto = (float) ($firstRow->total_monto ?? 0);

            // Extraemos solo los datos de detalle (grupo, concepto, monto)
            $data = collect($result)->map(function($r) {
                return [
                    'grupo' => $r->grupo,
                    'concepto' => $r->concepto,
                    'monto' => (float) $r->monto,
                ];
            })->all();
        }

        // Respuesta JSON en formato estable
        return response()->json([
            'success'       => true,
            'data'          => $data,
            'current_page'  => $currentPage,
            'last_page'     => $lastPage,
            'per_page'      => $perPage,
            'total'         => $total,
            'total_monto'   => $totalMonto,
        ]);
    }
}