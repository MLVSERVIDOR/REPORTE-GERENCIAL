<?php
protected $routeMiddleware = [
    // ...
    'logged' => \App\Http\Middleware\CheckLogged::class,
];