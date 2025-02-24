<?php
/* Añadir return false y true, en todos los METODOS */

namespace App\Controllers;

use App\Models\Actividades;

class ActividadesController {
    private $requestMethod;
    private $centroId;
    private $actividadId;
    private $model;

    public function __construct($requestMethod, $centroId = null, $actividadId = null) {
        $this->requestMethod = $requestMethod;
        $this->centroId = $centroId;
        $this->actividadId = $actividadId;
        $this->model = Actividades::getInstancia();
    }

    public function processRequest() {
        $response = null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($this->centroId) {
                    // Obtener actividades asociadas a un centro cívico
                    $response = $this->getActividadesPorCentro($this->centroId);
                } elseif ($this->actividadId) {
                    // Obtener una actividad específica
                    $response = $this->getActividad($this->actividadId);
                } else {
                    // Obtener todas las actividades (con filtros si los hay)
                    $response = $this->getAllActividades($_GET);
                }
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

    private function getActividadesPorCentro($centroId) {
        $result = $this->model->get(null, ["centro_civico_id" => $centroId]);
        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getActividad($id) {
        $result = $this->model->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getAllActividades($filters = []) {
        $result = $this->model->get(null, $filters);
        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(["mensaje" => "Recurso no encontrado"]);
        return $response;
    }
}
