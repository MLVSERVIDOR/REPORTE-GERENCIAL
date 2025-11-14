<?php
// app/Http/Controllers/Auth/LoginController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * GET /login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * POST /login
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'usuario'  => ['required','string'],
            'password' => ['required','string','min:6'],
        ]);

        $u = $data['usuario'];
        $p = $data['password'];

        // Asegúrate de usar los nombres de parámetros EXACTOS de tu SP
        // (si tu SP usa @usuario y @pass, déjalo así)
        $rows = DB::select(
            'EXEC Reporte.sp_LoginUsuario @usuario = ?, @pass = ?',
            [$u, $p]
        );

        if (empty($rows) || !(int)($rows[0]->ok ?? 0)) {
            $msg = $rows[0]->mensaje ?? 'Usuario o contraseña inválidos.';
            return back()->withErrors(['usuario' => $msg])->withInput();
        }

        $usr = $rows[0];

        // Si tu SP devuelve el nombre del área como "area_nombre" o "nombre"
        $areaNombre = $usr->area_nombre ?? ($usr->nombre ?? null);

        Session::put('usuario', [
            'id'           => $usr->id_usuario,
            'usuario'      => $usr->usuario,
            'nombres'      => $usr->nombres,
            'apellidos'    => $usr->apellidos,
            'area_codigo'  => $usr->area,       // código/clave de área
            'area_nombre'  => $areaNombre,      // nombre legible del área
            'estado'       => $usr->estado,
        ]);

        // Regenera el ID de sesión
        $request->session()->regenerate();

        // Redirige a la ruta "home" (asegúrate de tenerla definida)
        //return redirect()->intended(route('home'));
        //return redirect()->intended(route('dashboards'));
        return redirect()->intended(route('dashboards'));
    }

    /**
     * GET /home
     * Vista principal: carga datos de sesión y los pasa a la vista.
     */
    public function home()
    {
        $usuario = Session::get('usuario', []);
        if (empty($usuario)) {
            // Si no hay sesión, manda al login
            return redirect()->route('login');
        }

        return view('home', compact('usuario'));
    }

    /**
     * POST /logout
     */
    public function logout(Request $request)
    {
        Session::forget('usuario');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
