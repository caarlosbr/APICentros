<?php
// Cargar archivos y dependencias necesarias
require "../bootstrap.php";

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
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

// Manejo de solicitudes HTTP OPTIONS (usado en CORS para preflight requests)
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die(); // Termina la ejecución si es una solicitud de tipo OPTIONS
}

// Debug: Mostrar método HTTP y URL solicitada (útil para depuración)
/* var_dump("Método HTTP:", $_SERVER['REQUEST_METHOD']);
var_dump("URL solicitada:", $_SERVER['REQUEST_URI']); */

/**
 * Función para decodificar el token JWT y obtener el ID del usuario autenticado.
 * Devuelve el ID del usuario o NULL si el token es inválido o no está presente.
 */
function decodificarToken() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return null;
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1] ?? null;

    if (!$jwt) {
        return null;
    }

    try {
        $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
        return $decoded->sub; // Se asume que el ID del usuario está en 'sub'
    } catch (Exception $e) {
        return null;
    }
}

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
    'name' => 'get-user',
    'path' => '/^\/api\/user$/',
    'action' => UsuariosController::class,
    'perfil' => "Usuario"
]);

// Actualizar usuario (autenticado)
$router->add([
    'name' => 'update-user',
    'path' => '/^\/api\/user$/',
    'action' => UsuariosController::class,
    'perfil' => "Usuario"
]);

// Eliminar usuario (autenticado)
$router->add([
    'name' => 'delete-user',
    'path' => '/^\/api\/user$/',
    'action' => UsuariosController::class,
    'perfil' => "Usuario"
]);


// Rutas de Centros Cívicos 
$router->add([
    'name' => 'centros',
    'path' => '/^\/api\/centros(\/[0-9]+)?$/',
    'action' => CentrosCivicosController::class,
/*     'perfil' => "Usuario" // Indica que esta ruta requiere autenticación
 */]);

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
    'path' => '/^\/api\/reservas$/',
    'action' => InscripcionesController::class,
    'perfil' => "Usuario"
]);

$router->add([
    'name' => 'eliminar-inscripcion',
    'path' => '/^\/api\/reservas\/[0-9]+$/',
    'action' => InscripcionesController::class,
    'perfil' => "Usuario"
]);

/**
 * Buscar la ruta coincidente en el enrutador.
 * Si la ruta existe, se procesa la solicitud. Si no, se devuelve un error 404.
 */
$route = $router->match($request);
$usuarioId = null;

if ($route) {
    // Si la ruta requiere autenticación, verificar el token
    if (isset($route['perfil']) && $route['perfil'] === "Usuario") {
        $usuarioId = decodificarToken();

        var_dump($usuarioId);
        // Si no hay usuario autenticado, devolver un error 401
        if (!$usuarioId) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['mensaje' => 'No autorizado']);
            die();
        }

        // Si la URL contiene un ID de recurso, verificar que el usuario autenticado coincide con el recurso solicitado
        if ($recursoId !== null && $usuarioId !== $recursoId) {
            var_dump("Comparando usuarioId:", $usuarioId, "con recursoId:", $recursoId); // DEBUG
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['mensaje' => 'Acceso prohibido. No puedes acceder a este recurso.']);
            die();
        }
    }

    // Determinar el parámetro a enviar al controlador: si es ruta autenticada, se pasa el usuario; de lo contrario, el ID del recurso.
    $param = (isset($route['perfil']) && $route['perfil'] === "Usuario") ? $usuarioId : $recursoId;
    
    // Instanciar el controlador correspondiente a la ruta encontrada
    $controllerName = $route['action'];
    $controller = new $controllerName($requestMethod, $param);
    $controller->processRequest();
} else {
    // Si la ruta no coincide con ninguna definida, devolver error 404
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['mensaje' => 'Recurso no encontrado']);
}
