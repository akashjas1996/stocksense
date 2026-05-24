<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Helpers/db.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'samesite' => 'Lax']);
session_start();

// Simple front controller router
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = parse_url(APP_URL, PHP_URL_PATH);          // e.g. /stocksense/public
$path   = '/' . ltrim(substr($uri, strlen($base)), '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET'  => [
        '/'                       => 'DashboardController@index',
        '/auth/login'             => 'AuthController@loginForm',
        '/auth/logout'            => 'AuthController@logout',
        '/auth/register'          => 'AuthController@registerForm',
        '/rooms'                  => 'RoomController@index',
        '/rooms/create'           => 'RoomController@createForm',
        '/rooms/(\d+)'            => 'RoomController@show',
        '/rooms/(\d+)/edit'       => 'RoomController@editForm',
        '/rooms/(\d+)/qr'         => 'RoomController@qr',
        '/containers/(\d+)'       => 'ContainerController@show',
        '/containers/(\d+)/edit'  => 'ContainerController@editForm',
        '/containers/(\d+)/qr'   => 'ContainerController@qr',
        '/containers/create'      => 'ContainerController@createForm',
        '/inventory/create'       => 'InventoryController@createForm',
        '/inventory/(\d+)/edit'   => 'InventoryController@editForm',
        '/inventory/(\d+)/consume'=> 'InventoryController@consumeForm',
        '/scan'                   => 'ScanController@index',
        '/scan/location'          => 'ScanController@location',   // ?qr=UUID
        '/scan/product'           => 'ScanController@product',    // ?barcode=EAN
    ],
    'POST' => [
        '/auth/login'             => 'AuthController@login',
        '/auth/register'          => 'AuthController@register',
        '/rooms/store'            => 'RoomController@store',
        '/rooms/(\d+)/update'     => 'RoomController@update',
        '/rooms/(\d+)/delete'     => 'RoomController@delete',
        '/containers/store'       => 'ContainerController@store',
        '/containers/(\d+)/update'=> 'ContainerController@update',
        '/containers/(\d+)/delete'=> 'ContainerController@delete',
        '/inventory/store'        => 'InventoryController@store',
        '/inventory/(\d+)/update' => 'InventoryController@update',
        '/inventory/(\d+)/delete' => 'InventoryController@delete',
        '/inventory/(\d+)/consume'=> 'InventoryController@consume',
    ],
];

$dispatched = false;
foreach ($routes[$method] ?? [] as $pattern => $handler) {
    $regex = '#^' . $pattern . '$#';
    if (preg_match($regex, $path, $matches)) {
        array_shift($matches);
        [$class, $action] = explode('@', $handler);
        $file = __DIR__ . "/../src/Controllers/{$class}.php";
        if (file_exists($file)) {
            require_once $file;
            $controller = new $class();
            $controller->$action(...$matches);
        } else {
            http_response_code(500);
            echo "Controller not found: {$class}";
        }
        $dispatched = true;
        break;
    }
}

if (!$dispatched) {
    http_response_code(404);
    require __DIR__ . '/../src/Views/404.php';
}
