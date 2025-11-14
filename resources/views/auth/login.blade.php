<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>Login | Municipalidad Distrital de la Victoria</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="assets/images/logo-sm.png">

    <script src="assets/js/layout.js"></script>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />

    {{-- Overlay spinner styles --}}
    <style>
      .login-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
      }
      .login-overlay__box {
        color: #fff;
        text-align: center;
        padding: 24px 28px;
        border-radius: 12px;
        background: rgba(0,0,0,.35);
        backdrop-filter: blur(2px);
        min-width: 240px;
      }
      .d-none { display: none !important; }
    </style>
</head>

<body>
    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="index-2.html" class="d-inline-block auth-logo">
                                    <img src="assets/images/logo.png" alt="" height="150">
                                </a>
                            </div>
                            <p class="mt-3 fs-15 fw-medium">REPORTES GERENCIALES</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card card-bg-fill">
                            <br>
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">INICIAR SESIÓN</h5>
                                    <p class="text-muted">Acceso restringido sólo para usuarios autorizados. Por favor ingrese su usuario y contraseña.</p>
                                </div>

                                @if($errors->any())
                                    <div class="alert border-0 alert-danger material-shadow" role="alert">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <div class="p-2 mt-1">
                                    {{-- FORM CON IDS PARA MANEJAR SPINNER --}}
                                    <form id="login-form" method="POST" action="{{ route('login.post') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="usuario" class="form-label">Usuario</label>
                                            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingrese su Usuario" value="{{ old('usuario') }}" required autofocus>
                                            @error('usuario') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Contraseña</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input" id="password-input" placeholder="Ingrese su contraseña" name="password" required minlength="6">
                                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            {{-- BOTÓN CON SPINNER --}}
                                            <button id="btnLogin" type="submit" class="btn btn-success w-100">
                                                Ingresar
                                                <!--<span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>-->
                                            </button>
                                        </div>
                                        <br>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy;
                                <script>document.write(new Date().getFullYear())</script> Municipalidad Distrital de la Victoria.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    {{-- OVERLAY SPINNER (PANTALLA COMPLETA) --}}
    <div id="page-spinner" class="login-overlay d-none" aria-hidden="true">
      <div class="login-overlay__box">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
        <div class="mt-3">Validando credenciales...</div>
      </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>

    <script src="assets/libs/particles.js/particles.js"></script>
    <script src="assets/js/pages/particles.app.js"></script>
    <script src="assets/js/pages/password-addon.init.js"></script>

    {{-- JS PARA MOSTRAR SPINNER EN SUBMIT --}}
    <script>
      (function () {
        const form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', function () {
          const btn = document.getElementById('btnLogin');
          const overlay = document.getElementById('page-spinner');

          // Evitar doble submit
          if (btn) {
            btn.disabled = true;
            btn.classList.add('disabled');

            const label = btn.querySelector('.btn-label');
            const spin  = btn.querySelector('.spinner-border');
            if (label) label.textContent = 'Ingresando...';
            if (spin)  spin.classList.remove('d-none');
          }

          // Mostrar overlay
          if (overlay) overlay.classList.remove('d-none');

          // No hacemos preventDefault: dejamos que el form envíe normal
          // El spinner se oculta automáticamente al cargar la siguiente vista
        });
      })();

       (function () {
            const lf = document.getElementById('login-form');
            if (!lf) return;
            lf.addEventListener('submit', function () {
            try { sessionStorage.setItem('showDashOverlay', '1'); } catch(e){}
            });
        })();
    </script>
</body>
</html>
