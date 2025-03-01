<?php
// Cargar archivos y dependencias necesarias
require "../bootstrap.php";
require "../app/Functions/codificarToken.php"; // Incluir el archivo que contiene la función decodificarToken

use App\Core\Router;
use App\Controllers\CentrosCivicosController;
use App\Controllers\InstalacionesController;
use App\Controllers\ActividadesController;
use App\Controllers\ReservasController;
use App\Controllers\InscripcionesController;
use App\Controllers\UsuariosController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// Configuración de cabeceras para permitir solicitudes desde cualquier origen (CORS)
header("Access-Control-Allow-Origin: http://192.168.0.106:3000");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");


// Manejo de solicitudes HTTP OPTIONS (usado en CORS para preflight requests)
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('HTTP/1.1 200 OK');
    die(); // Termina la ejecución si es una solicitud de tipo OPTIONS
}

// Debug: Mostrar método HTTP y URL solicitada (útil para depuración)
/* var_dump("Método HTTP:", $_SERVER['REQUEST_METHOD']);
var_dump("URL solicitada:", $_SERVER['REQUEST_URI']); */

// Obtener la URI solicitada y dividirla en segmentos
$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $request);

// Obtener el ID del recurso (si está presente en la URL)
$recursoId = isset($uri[3]) && is_numeric($uri[3]) ? (int)$uri[3] : null; // Obtener el ID del recurso desde la URL si es numérico, de lo contrario asignar null

// Configuración del enrutador con rutas protegidas y públicas
$router = new Router();

/**
 * RUTAS PARA USUARIOS
 */
// Registro de usuario (público)
$router->add([
    'name' => 'register',
    'path' => '/^\/api\/register$/',
    'action' => UsuariosController::class
]);

// Inicio de sesión (público)
$router->add([
    'name' => 'login',
    'path' => '/^\/api\/login$/',
    'action' => UsuariosController::class
]);

// Renovación de token (autenticado)
$router->add([
    'name' => 'refresh-token',
    'path' => '/^\/api\/token\/refresh$/',
    'action' => UsuariosController::class,
    'perfil' => "Usuario"
]);

// Obtener información del usuario autenticado (autenticado)
$router->add([
    'name' => 'user',
    'path' => '/^\/api\/user$/',
    'action' => UsuariosController::class,
    'perfil' => "Usuario"
]);



// Rutas de Centros Cívicos 
$router->add([
    'name' => 'centros',
    'path' => '/^\/api\/centros(\/[0-9]+)?$/',
    'action' => CentrosCivicosController::class,
]);

// Rutas de Instalaciones
$router->add([
    'name' => 'instalaciones-centro',
    'path' => '/^\/api\/centros\/[0-9]+\/instalaciones$/',
    'action' => InstalacionesController::class
]);

$router->add([
    'name' => 'instalaciones',
    'path' => '/^\/api\/instalaciones(\/[0-9]+)?$/',
    'action' => InstalacionesController::class
]);

// Rutas de Actividades
$router->add([
    'name' => 'actividades-centro',
    'path' => '/^\/api\/centros\/[0-9]+\/actividades$/',
    'action' => ActividadesController::class
]);

$router->add([
    'name' => 'actividades',
    'path' => '/^\/api\/actividades(\/[0-9]+)?$/',
    'action' => ActividadesController::class
]);

// Rutas de Reservas (Requiere autenticación)
$router->add([
    'name' => 'reservas',
    'path' => '/^\/api\/reservas(\/[0-9]+)?$/',
    'action' => ReservasController::class,
    'perfil' => "Usuario"
]);

// Rutas de Inscripciones (Requiere autenticación)
$router->add([
    'name' => 'crear-inscripcion',
    'path' => '/^\/api\/inscripciones$/',
    'action' => InscripcionesController::class,
    'perfil' => "Usuario"
]);

$router->add([
    'name' => 'eliminar-inscripcion',
    'path' => '/^\/api\/inscripciones\/[0-9]+$/',
    'action' => InscripcionesController::class,
    'perfil' => "Usuario"
]); 

// Buscar la ruta que coincide con la solicitud
$route = $router->match($request);
$usuarioId = null; 

if ($route) {
    // Nombre de la clase controladora que maneja esta ruta
    $controllerName = $route['action'];
    
    // Si la ruta requiere autenticación (perfil "Usuario"), verificamos el token
    if (isset($route['perfil']) && $route['perfil'] === "Usuario") {
        // Obtenemos el ID de usuario desde el token JWT
        $usuarioId = decodificarToken();

        // Si no hay usuario autenticado (token inválido o ausente), devolvemos un 401
        if (!$usuarioId) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['mensaje' => 'No autorizado']);
            die();
        }

        // Instanciamos el controlador con tres parámetros
        $controller = new $controllerName($requestMethod, $recursoId, $usuarioId); // Pasar el ID de usuario al controlador, para rutas protegidas, para que pueda acceder a la ID del usuario
    } else {
        // Ruta pública: no necesita ID de usuario
        $controller = new $controllerName($requestMethod, $recursoId);
    }

    // Procesar la solicitud en el controlador
    $controller->processRequest();

} else {
    // Si la ruta no coincide con ninguna definida, devolvemos 404
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['mensaje' => 'Recurso no encontrado']);
}
