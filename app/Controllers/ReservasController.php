<?php
/* Añadir return false y true, en todos los METODOS */


namespace App\Controllers;

use App\Models\Reservas;
/* require_once __DIR__ . '/../Functions/codificarToken.php';
 */class ReservasController {
    private $requestMethod;
    private $reservaId;
    private $usuarioId;
    private $model;

    public function __construct($requestMethod, $reservaId = null, $usuarioId = null) {
        $this->requestMethod = $requestMethod;
        $this->reservaId = $reservaId;
        $this->usuarioId = $usuarioId;
        $this->model = Reservas::getInstancia();
    }

    public function processRequest() {
        $response = null;

        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getReservas();
                break;
            case 'POST':
                $response = $this->crearReserva();
                break;
            case 'DELETE':
                $response = $this->eliminarReserva($this->reservaId);
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

    

    private function getReservas() {
        // var_dump("Dentro de getReservas:");
        if ($this->reservaId) {
            // Obtener reserva concreta por $this->reservaId
            $result = $this->model->getById($this->reservaId);
        } elseif ($this->usuarioId) {
            // Obtener reservas del usuario
            $result = $this->model->get($this->usuarioId);
        }
    
       // var_dump($this->usuarioId); // para depurar
    
        if (!$result) {
            return $this->notFoundResponse();
        }
    
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    

    private function crearReserva() {
        $input = (array) json_decode(file_get_contents('php://input'), true);
    
        // Asegurar que la reserva esté vinculada al usuario autenticado
        if (!$this->usuarioId) {
            return $this->unprocessableEntityResponse();
        }

        // Agregar el usuario_id a los datos de la reserva
        $input['usuario_id'] = $this->usuarioId;

        // Validar los datos recibidos
        if (!$this->validateReserva($input)) {
            return $this->unprocessableEntityResponse();
        }
    
        // Llamar al modelo para crear la reserva
        $this->model->set($input);
    
        // Respuesta de éxito
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['mensaje' => 'Reserva creada exitosamente']);
        return $response;
    }
    

    private function eliminarReserva($id) {
        if (empty($id) || !$this->usuarioId) {
            return $this->unprocessableEntityResponse();
        }
    
        // Obtener la reserva antes de eliminar
        $result = $this->model->getById($id);
    
        if (!$result) {
            return $this->notFoundResponse(); // La reserva no existe
        }
    
        // Verificar que el usuario autenticado es el dueño de la reserva
        if ($result['usuario_id'] !== $this->usuarioId) {
            return $this->unprocessableEntityResponse();
        }
    
        // Eliminar la reserva
        $this->model->delete($id);
    
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Reserva eliminada exitosamente']);
        return $response;
    }
    

    private function validateReserva($input) {
        return isset($input['nombre_solicitante']) &&
               isset($input['telefono']) &&
               isset($input['correo_electronico']) &&
               isset($input['instalacion_id']) &&
               isset($input['fecha_inicio']) &&
               isset($input['fecha_final']) &&
               isset($input['estado']);
    }

    private function unprocessableEntityResponse() {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['error' => 'Datos inválidos o incompletos']);
        return $response;
    }

    private function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(['mensaje' => 'Recurso no encontrado']);
        return $response;
    }

    public function getByUsuarioId($usuario_id){
        $result = $this->model->get($usuario_id);
        return $result;
    }

    public function getById($id){
        $result = $this->model->getById($id);
        return $result;
    }
}
