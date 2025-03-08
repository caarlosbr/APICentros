<?php

namespace App\Controllers; // Define el espacio de nombres para la clase

use App\Models\Usuarios; // Importa la clase Usuarios del modelo

class UsuariosController
{
    // Propiedades para almacenar el método de petición, el id de usuario y la instancia del modelo
    private $requestMethod;
    private $usuarioId;
    private $model;
    private $reservaId;

    // Constructor de la clase: inicializa las propiedades y obtiene la instancia del modelo Usuarios
    public function __construct($requestMethod, $recursoId = null, $usuarioId = null)
    {
        $this->requestMethod = $requestMethod;
        $this->reservaId = $recursoId;  // para identificar el recurso
        $this->usuarioId = $usuarioId;
        $this->model = Usuarios::getInstancia(); // Obtiene la instancia del modelo 
    }
    
    // Método principal que procesa la petición HTTP y dirige a la función correspondiente
    public function processRequest()
    {
        $response = null;
        // Se evalúa el método HTTP de la petición
        switch ($this->requestMethod) {
            case 'POST':
                // Se comprueba la ruta para determinar la acción a ejecutar
                if ($_SERVER['REQUEST_URI'] === "/api/register") { // Si la ruta es /api/register, se registra un usuario
                    $response = $this->registerUser();
                } elseif ($_SERVER['REQUEST_URI'] === "/api/login") {  // Si la ruta es /api/login, se inicia sesión
                    $response = $this->loginUser();
                } elseif ($_SERVER['REQUEST_URI'] === "/api/token/refresh") { // Si la ruta es /api/token/refresh, se renueva el token
                    $response = $this->refreshToken();
                }
                break;
            case 'GET':
                $response = $this->getUser();
                break;
            case 'PUT':
                $response = $this->updateUser();
                break;
            case 'DELETE':
                $response = $this->deleteUser();
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        // Envía la respuesta al cliente: establece el encabezado HTTP y muestra el cuerpo de la respuesta
        if ($response) { // Si hay una respuesta
            header($response['status_code_header']); // Establece el encabezado HTTP con el código de estado
            if ($response['body']) { // Si hay un cuerpo en la respuesta 
                echo $response['body']; // Muestra el cuerpo de la respuesta
            }
        } else {
            header('HTTP/1.1 500 Internal Server Error'); // Si no hay respuesta, se envía un error 500
            echo json_encode(['error' => 'Unexpected error']); // Muestra un mensaje de error
        }
    }

    // **Registrar usuario (POST /api/register)**
    private function registerUser()
    {
        // Lee y decodifica la entrada JSON
        $input = json_decode(file_get_contents('php://input'), true);

        // Valida que se hayan proporcionado los datos requeridos
        if (!$this->validateUserInput($input)) {
            return $this->unprocessableEntityResponse("Faltan datos requeridos."); // Retorna un error 422
        } 

        // Intenta insertar el usuario usando el modelo
        $result = $this->model->set($input);

        // Si falla la inserción, retorna un error
        if (!$result) {
            return $this->unprocessableEntityResponse("El usuario ya está registrado o hubo un error en la inserción."); // Retorna un error 422
        }

        // Configura la respuesta exitosa con código 201 Created
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['mensaje' => 'Usuario registrado exitosamente']);
        return $response;
    }

    // **Inicio de sesión (POST /api/login)**
    private function loginUser()
    {
        // Lee y decodifica la entrada JSON
        $input = (array) json_decode(file_get_contents('php://input'), true);

        // Verifica que se hayan enviado tanto email como contraseña
        if (empty($input['email']) || empty($input['password'])) {
            return $this->unprocessableEntityResponse();
        }

        // iniciar sesión y obtener el token
        $token = $this->model->login($input['email'], $input['password']);

        // Si las credenciales son incorrectas, retorna un error
        if (!$token) {
            return $this->unprocessableEntityResponse("Credenciales incorrectas.");
        }

        // Configura la respuesta exitosa con el token
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['token' => $token]); // Retorna el token en el cuerpo de la respuesta
        return $response;
    }

    // **Renovación de token (POST /api/token/refresh)**
    private function refreshToken()
    {
        // Obtiene el encabezado de autorización de la solicitud HTTP
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
        // Inicializa la variable donde se almacenará el token
        $token = null;
    
        // Si se encontró el encabezado, se extrae el token, tiene que ser fomato Bearer (token)
        if (!empty($authHeader)) { // Si se encontró el encabezado, es decir si se proporcionó el token
            $parts = explode(" ", $authHeader); // Divide el encabezado en dos partes (Bearer y el token)
            $token = $parts[1] ?? null; // Obtiene el token de la segunda parte
        }
    
        // Si no se proporcionó el token, retorna un error 422
        if (!$token) {
            return $this->unprocessableEntityResponse("Token no proporcionado.");
        }
    
        // Se envía el token al modelo para validarlo y generar uno nuevo
        $newToken = $this->model->refrescarToken($token);
    
        // Si no se pudo generar un nuevo token, significa que el token era inválido o expiró
        if (!$newToken) {
            return $this->unprocessableEntityResponse("Token inválido o expirado.");
        }
    
        // Configura la respuesta exitosa con el nuevo token
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['token' => $newToken]);
        return $response;
    }
    
    // **Obtener información del usuario (GET /api/user)**
    private function getUser()
    {
        // Obtiene la información del usuario desde el modelo usando el id proporcionado
        $result = $this->model->get($this->usuarioId);
        /* var_dump($this->usuarioId); */ // Mensaje de depuración

        // Si no se encontró el usuario, retorna una respuesta de recurso no encontrado
        if (!$result) {
            return $this->notFoundResponse();
        }

        // Configura la respuesta exitosa con la información del usuario
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // **Actualizar usuario (PUT /api/user)**
    private function updateUser()
    {
        // Lee y decodifica la entrada JSON
        $input = (array) json_decode(file_get_contents('php://input'), true);

        // Valida que se hayan enviado los datos requeridos para actualizar
        if (!$this->validateUserUpdateInput($input)) {
            return $this->unprocessableEntityResponse();
        }

        // Llama al modelo para actualizar el usuario con la nueva información
        $this->model->edit($this->usuarioId,$input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Usuario actualizado correctamente']);
        return $response;
    }

    // **Eliminar usuario (DELETE /api/user)**
    private function deleteUser()
    {
        // Verifica que se haya proporcionado el id del usuario
        if (!$this->usuarioId) {
            return $this->unprocessableEntityResponse();
        }

        // Llama al modelo para eliminar el usuario
        $result =  $this->model->delete($this->usuarioId);

        // Si no se pudo eliminar (usuario no encontrado u otro error), retorna error 404
        if(!$result) {
            return $this->notFoundResponse();
        }

        // Configura la respuesta exitosa
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Usuario eliminado correctamente']);
        return $response;
    }

    // **Validar entrada de datos para registrar usuario**
    private function validateUserInput($input)
    {
        // Se comprueba que existan los campos: nombre, email y password
        return isset($input['nombre']) && isset($input['email']) && isset($input['password']);
    }

    // **Validar entrada de datos para actualizar usuario**
    private function validateUserUpdateInput($input)
    {
        // Se comprueba que existan los campos: id, nombre, email y password
        return isset($input['nombre']) && isset($input['email']) && isset($input['password']);
    }

    // **Generar respuesta de error para datos inválidos o incompletos (HTTP 422)**
    private function unprocessableEntityResponse($message = "Datos inválidos o incompletos.")
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['error' => $message]);
        return $response;
    }

    // **Generar respuesta de recurso no encontrado (HTTP 404)**
    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(['mensaje' => 'Recurso no encontrado']);
        return $response;
    }
}
