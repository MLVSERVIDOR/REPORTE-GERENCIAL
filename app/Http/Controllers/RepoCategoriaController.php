<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class RepoCategoriaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'area'  => ['nullable', 'string'],
        ]);

        $desde = $request->input('desde') ?: Carbon::now()->startOfMonth()->toDateString();
        $hasta = $request->input('hasta') ?: Carbon::now()->toDateString();
        $area  = $request->input('area');

        Log::info('→ INICIO: Proceso básico');

        $rows = DB::select('EXEC Reporte.sp_BuscarFecha_2025 @desde=?, @hasta=?, @area=?', [$desde, $hasta, $area]);

        // Excluir tipo_tra=99
        $rows = array_values(array_filter($rows, function ($r) {
            $tt = $r->tipo_tra ?? null;
            return is_null($tt) || (string)$tt !== '99';
        }));

        // Normalizador de partida (quita espacios)
        $normalize = function ($code) {
            $code = trim((string)$code);
            $code = preg_replace('/\s+/', '', $code);
            return $code;
        };

        $rowsOut = array_map(function ($r) use ($normalize) {
            // stdClass -> array para poder manipular claves
            $a = (array)$r;

            // tomar cod_partida o fallback a partida
            $raw = $a['partida'] ?? $a['partida'] ?? null;

            // Si por alguna razón la clave llega con spaces/encoding raro
            if ($raw === null) {
                foreach ($a as $k => $v) {
                    if (trim($k) === 'partida' && $v !== null && $v !== '') {
                        $raw = $v;
                        break;
                    }
                }
            }

            $raw  = is_null($raw) ? '' : (string)$raw;
            $norm = $normalize($raw);

            // 👇 Aseguramos que en el JSON salgan TODAS estas claves:
            $a['partida']      = $raw;   // <-- pediste que salga cod_partida
            $a['partida_raw']  = $raw;   // (por si lo lees así en el front)
            $a['partida_norm'] = $norm;  // normalizado sin espacios

            // Asegurar monto numérico (sin romper otros gráficos)
            $a['monto'] = isset($a['monto'])
                ? (float)$a['monto']
                : (isset($a['total_1']) ? (float)$a['total_1'] : 0);

            return $a;
        }, $rows);

        // (opcional) totales por categoría (fallback por texto)
        $cats = ['Predial'=>0,'Arbitrios'=>0,'Multas Tributarias'=>0];
        foreach ($rowsOut as $r) {
            $txt = strtoupper($r['grupo'] ?? $r['concepto'] ?? $r['tipo'] ?? '');
            $m   = (float)($r['monto'] ?? 0);
            if (strpos($txt,'PREDIAL')   !== false) { $cats['Predial']   += $m; continue; }
            if (strpos($txt,'ARBITRIO')  !== false) { $cats['Arbitrios'] += $m; continue; }
            if (strpos($txt,'MULTA')     !== false) { $cats['Multas Tributarias'] += $m; continue; }
        }
        $seriesData = [
            ['name'=>'Predial','value'=>round($cats['Predial'],2)],
            ['name'=>'Arbitrios','value'=>round($cats['Arbitrios'],2)],
            ['name'=>'Multas Tributarias','value'=>round($cats['Multas Tributarias'],2)],
        ];
        $totales = [
            'Predial'            => $seriesData[0]['value'],
            'Arbitrios'          => $seriesData[1]['value'],
            'Multas Tributarias' => $seriesData[2]['value'],
        ];
        $totalGeneral = array_sum($totales);


        Log::info('✓ FIN: Proceso básico completado exitosamente');

        return view('dashboards', [
            'rows'         => $rowsOut,     // 👈 ahora incluye cod_partida, cod_partida_raw y cod_partida_norm
            'seriesData'   => $seriesData,
            'totales'      => $totales,
            'totalGeneral' => $totalGeneral,
            'desde'        => $desde,
            'hasta'        => $hasta,
            'area'         => $area,
            'totalMonto' => $totalMonto ?? 0,
            
        ]);
    }
}
