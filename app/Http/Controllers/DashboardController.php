<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function totalGeneral(Request $r)
{
    try {
        $area = $r->query('area');
        $area = ($area === '' || $area === null) ? null : $area;

        $d = \Carbon\Carbon::parse($r->query('desde'))->toDateString();
        $h = \Carbon\Carbon::parse($r->query('hasta'))->toDateString();

        $row = \DB::connection('sqlsrv')->selectOne(
            'EXEC Reporte.sp_BuscarFecha_2025_2_bak @desde = ?, @hasta = ?, @area = ?',
            [$d, $h, $area]
        );

        $total = (float)($row->total ?? 0);
        return response()->json(['total'=>$total]);
    } catch (\Throwable $e) {
        Log::error('totalGeneral error', ['ex'=>$e, 'desde'=>$r->query('desde'), 'hasta'=>$r->query('hasta'), 'area'=>$r->query('area')]);
        return response()->json([
            'error' => 'No se pudo calcular el total',
            'detail' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
        ], 500);
    }
}
}
