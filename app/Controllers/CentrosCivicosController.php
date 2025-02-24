<?php
/* Añadir return false y true, en todos los METODOS */

namespace App\Controllers;

use App\Models\CentrosCivicos;

class CentrosCivicosController {
    private $requestMethod;
    private $centroId;
    private $model;

    public function __construct($requestMethod, $centroId = null) {
        $this->requestMethod = $requestMethod;
        $this->centroId = $centroId;
        $this->model = CentrosCivicos::getInstancia();
    }

    public function processRequest() {
        $response = null;
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->centroId) {
                    $response = $this->getCentro($this->centroId);
                } else {
                    $response = $this->getAllCentros();
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        if ($response) {
            header($response['status_code_header']);
            if ($response['body']){
                echo $response['body'];
            }
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Unexpected error']);
        }
    }

    private function getCentro($id) {
        $result = $this->model->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getAllCentros() {
        $result = $this->model->get();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function notFoundResponse(){
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

}
