<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RepoTupaTusneController extends Controller
{
    public function index(Request $request)
    {
        // Fechas por defecto: inicio de mes -> hoy
        $desde = $request->input('desde') ?: Carbon::now()->startOfMonth()->toDateString();
        $hasta = $request->input('hasta') ?: Carbon::now()->toDateString();
        $area  = $request->input('area');

        // Llama tu SP (ya filtra por fechas/área)
        $rows = DB::select(
            'EXEC Reporte.sp_BuscarFecha_2025 @desde = ?, @hasta = ?, @area = ?',
            [$desde, $hasta, $area]
        );

        // (Opcional) total monto excluyendo tipo_tra = 99
        $totalMonto = collect($rows)->sum(function ($r) {
            return (string)($r->tipo_tra ?? '') === '99' ? 0 : (float)($r->monto ?? 0);
        });

        return view('dashboards', [
            'rows'       => $rows,      // 👈 NECESARIO
            'desde'      => $desde,
            'hasta'      => $hasta,
            'area'       => $area,
            'totalMonto' => $totalMonto ?? 0,
        ]);
    }
}
