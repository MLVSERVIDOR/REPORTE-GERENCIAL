<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FechaController;
use App\Http\Controllers\RepoCategoriaController;
use App\Http\Controllers\RepoTupaTusneController;
use App\Http\Controllers\RepoGerenciaController;
use App\Http\Controllers\DashboardController;


Route::redirect('/', '/login');

/*
|---------------------------------------------------------------------------
| Rutas públicas (guest)
|---------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

/*
|---------------------------------------------------------------------------
| Cerrar sesión
|---------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|---------------------------------------------------------------------------
| Rutas protegidas (usuario en sesión)
| Si YA registraste el middleware 'logged', úsalo. Si no, usa 'auth' o
| quítalo hasta registrarlo (abajo te dejo cómo registrarlo).
|---------------------------------------------------------------------------
*/
Route::middleware('logged')->group(function () {
    // Home: pasa el usuario de la sesión a la vista
    Route::get('/home', function () {
        $usuario = session('usuario'); // ['id','usuario','nombres','apellidos','area_codigo','area_nombre','estado']
        return view('home', compact('usuario'));
    })->name('home');

    // Dashboards (si tu vista está en resources/views/dashboards.blade.php)
    Route::get('/dashboards', function () {
        return view('dashboards');
    })->name('dashboards');

    // Búsqueda por fechas (controlador)
    //Route::get('/fecha', [FechaController::class, 'index'])->name('fecha.index');
    
   Route::get('/dashboards', [RepoCategoriaController::class, 'index'])->name('dashboards');

  // Route::get('/dashboards', [FechaController::class, 'index'])->name('dashboards')->middleware('logged');
   Route::get('/dashboards', [RepoTupaTusneController::class, 'index'])->name('dashboards');
    Route::get('/dashboards', [RepoGerenciaController::class, 'index'])->name('dashboards');

    Route::get('/repo/hijos', [RepoGerenciaController::class, 'hijos'])->name('repo.hijos');
    Route::get('/repo/detalle', [RepoGerenciaController::class, 'detalleArea'])->name('repo.detalle');

  

// Vista del dashboard (genera variables para ECharts)
Route::get('/dashboards', [FechaController::class, 'index'])->name('dashboards')->middleware('logged');;

// API solo para el total (AJAX), NO toques esta desde el gráfico
Route::get('/api/total-general', [FechaController::class, 'apiTotalGeneral'])->name('api.total.general');

//Route::get('/repo/total-general', [DashboardController::class, 'totalGeneral'])->name('repo.total-general');

});
