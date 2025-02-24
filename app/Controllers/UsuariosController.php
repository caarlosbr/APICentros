<?php

namespace App\Controllers;

use App\Models\Usuarios;

class UsuariosController
{
    private $requestMethod;
    private $usuarioId;
    private $model;

    public function __construct($requestMethod, $usuarioId = null)
    {
        $this->requestMethod = $requestMethod;
        $this->usuarioId = $usuarioId;
        $this->model = Usuarios::getInstancia();
    }

    public function processRequest()
    {
        $response = null;
        switch ($this->requestMethod) {
            case 'POST':
                if ($_SERVER['REQUEST_URI'] === "/api/register") {
                    var_dump("Entrando a registerUser"); // Agrega esto para depurar
                    $response = $this->registerUser();
                } elseif ($_SERVER['REQUEST_URI'] === "/api/login") {
                    $response = $this->loginUser();
                } elseif ($_SERVER['REQUEST_URI'] === "/api/token/refresh") {
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

        if ($response) {
            header($response['status_code_header']);
            if ($response['body']) {
                echo $response['body'];
            }
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Unexpected error']);
        }
    }

    // **Registrar usuario (POST /api/register)**
    private function registerUser()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateUserInput($input)) {
            return $this->unprocessableEntityResponse("Faltan datos requeridos.");
        }

        $result = $this->model->set($input);

        if (!$result) {
            return $this->unprocessableEntityResponse("El usuario ya está registrado o hubo un error en la inserción.");
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['mensaje' => 'Usuario registrado exitosamente']);
        return $response;
    }

    // **Inicio de sesión (POST /api/login)**
    private function loginUser()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        if (empty($input['email']) || empty($input['password'])) {
            return $this->unprocessableEntityResponse();
        }

        $token = $this->model->login($input['email'], $input['password']);

        if (!$token) {
            return $this->unprocessableEntityResponse("Credenciales incorrectas.");
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['token' => $token]);
        return $response;
    }

    // **Renovación de token (POST /api/token/refresh)**
    private function refreshToken()
    {
        // Obtener el encabezado de autorización desde la solicitud HTTP.
        // Si no existe, asignamos una cadena vacía para evitar errores.
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
        // Inicializamos la variable donde almacenaremos el token extraído.
        $token = null;
    
        // Si el encabezado de autorización no está vacío, intentamos extraer el token.
        if (!empty($authHeader)) {
            // 🔹 Dividimos el encabezado en partes separadas por espacio (" "),
            // ya que suele tener el formato: "Bearer <token>".
            $parts = explode(" ", $authHeader);
    
            // El token real debería estar en la segunda posición del array resultante.
            // Si no existe, asignamos `null` para manejarlo más adelante.
            $token = $parts[1] ?? null;
        }
    
        // Si no se proporcionó un token en el encabezado, devolvemos un error 422 (Datos incompletos).
        if (!$token) {
            return $this->unprocessableEntityResponse("Token no proporcionado.");
        }
    
        // Enviamos el token a la función `refrescarToken()` del modelo para validarlo y generar uno nuevo.
        $newToken = $this->model->refrescarToken($token);
    
        // Si el modelo devuelve `null`, significa que el token es inválido o ha expirado.
        // Devolvemos un error 422 con un mensaje explicativo.
        if (!$newToken) {
            return $this->unprocessableEntityResponse("Token inválido o expirado.");
        }
    
        // Si el token es válido, configuramos una respuesta con código 200 OK.
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
    
        // En el cuerpo de la respuesta, enviamos el nuevo token generado en formato JSON.
        $response['body'] = json_encode(['token' => $newToken]);
    
        // Retornamos la respuesta al cliente.
        return $response;
    }
    

    // **Obtener información del usuario (GET /api/user)**
    private function getUser()
    {
        $result = $this->model->get($this->usuarioId);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // **Actualizar usuario (PUT /api/user)**
    private function updateUser()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        if (!$this->validateUserUpdateInput($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->model->edit($input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Usuario actualizado correctamente']);
        return $response;
    }

    // **Eliminar usuario (DELETE /api/user)**
    private function deleteUser()
    {
        if (!$this->usuarioId) {
            return $this->unprocessableEntityResponse();
        }

       $result =  $this->model->delete($this->usuarioId);

        if(!$result) {
            return $this->notFoundResponse();
        }


        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Usuario eliminado correctamente']);
        return $response;
    }

    // **Validar entrada de datos de usuario**
    private function validateUserInput($input)
    {
        return isset($input['nombre']) && isset($input['email']) && isset($input['password']);
    }

    private function validateUserUpdateInput($input)
    {
        return isset($input['id']) && isset($input['nombre']) && isset($input['email']) && isset($input['password']);
    }

    // **Respuestas de error**
    private function unprocessableEntityResponse($message = "Datos inválidos o incompletos.")
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['error' => $message]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(['mensaje' => 'Recurso no encontrado']);
        return $response;
    }
}
