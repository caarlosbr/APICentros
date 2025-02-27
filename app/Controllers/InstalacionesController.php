<?php
/* Añadir return false y true, en todos los METODOS */

namespace App\Controllers;

use App\Models\Instalaciones;

class InstalacionesController {
    private $requestMethod;
    private $centroId;
    private $instalacionId;
    private $model;

    public function __construct($requestMethod, $centroId = null, $instalacionId = null) {
        $this->requestMethod = $requestMethod;
        $this->centroId = $centroId;
        $this->instalacionId = $instalacionId;
        $this->model = Instalaciones::getInstancia();
    }

    public function processRequest() {
        $response = null;
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->centroId) {
                    // Obtener instalaciones por centro
                    $response = $this->getInstalacionesPorCentro($this->centroId);
                } elseif ($this->instalacionId) {
                    // Obtener una instalación específica
                    $response = $this->getInstalacion($this->instalacionId);
                } else {
                    // Obtener todas las instalaciones (con o sin filtros)
                    $response = $this->getAllInstalaciones($_GET);
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

    private function getInstalacionesPorCentro($centroId) {
        // Filtra instalaciones por centro_civico_id
        $result = $this->model->get(null, ["centro_civico_id" => $centroId]);
    
        if (!$result) {
            return $this->notFoundResponse();
        }
    
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result); // Devolver solo las instalaciones filtradas
        return $response;
    }
    

    private function getInstalacion($id) {
        $result = $this->model->get($id); // Busca instalación por ID
        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    
    private function getAllInstalaciones($filters = []) {
        $result = $this->model->get(null, $filters); // Pasa filtros desde $_GET al modelo
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

