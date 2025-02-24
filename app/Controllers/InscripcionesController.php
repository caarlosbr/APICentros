<?php
/* Añadir return false y true, en todos los METODOS */

namespace App\Controllers;

use App\Models\Inscripciones;
require_once __DIR__ . '/../Functions/codificarToken.php';

class InscripcionesController {
    private $requestMethod;
    private $inscripcionId;
    private $usuarioId;
    private $model;

    public function __construct($requestMethod, $inscripcionId = null, $usuarioId = null) {
        $this->requestMethod = $requestMethod;
        $this->inscripcionId = $inscripcionId;
        $this->usuarioId = $usuarioId;
        $this->model = Inscripciones::getInstancia();
    }

    public function processRequest() {
        $response = null;

        switch ($this->requestMethod) {
            case 'POST':
                $response = $this->crearInscripcion();
                break;
            case 'DELETE':
                $response = $this->eliminarInscripcion($this->inscripcionId);
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


    private function crearInscripcion() {
        $usuarioId = decodificarToken(); // Obtener ID del usuario autenticado
    
        if (!$usuarioId) {
            return $this->unauthorizedResponse(); // Devuelve 401 si el usuario no está autenticado
        }
    
        $input = (array) json_decode(file_get_contents('php://input'), true);
    
        // Agregar automáticamente el usuario autenticado al array de entrada
        $input['usuario_id'] = $usuarioId;
    
        if (!$this->validateInscripcion($input)) {
            return $this->unprocessableEntityResponse();
        }
    
        $this->model->set($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['mensaje' => 'Inscripción creada exitosamente']);
        return $response;
    }
    

    private function eliminarInscripcion($id) {
        if (empty($id)) {
            return $this->unprocessableEntityResponse();
        }

        $result = $this->model->delete($id);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['mensaje' => 'Inscripción eliminada exitosamente']);
        return $response;
    }

    private function validateInscripcion($input) {
        if (
            empty($input['nombre_solicitante']) ||
            empty($input['telefono']) ||
            empty($input['correo_electronico']) ||
            empty($input['actividad_id']) ||
            empty($input['estado'])
        ) {
            return false;
        }
    
        // Validación adicional para el estado (opcional)
        $estadosPermitidos = ['Aceptada', 'Lista de espera', 'Rechazada'];
        if (!in_array($input['estado'], $estadosPermitidos)) {
            return false;
        }
    
        return true;
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

    private function unauthorizedResponse() {
        $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
        $response['body'] = json_encode(['error' => 'Acceso denegado. Se requiere autenticación.']);
        return $response;
    }
    
}
