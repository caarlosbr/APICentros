<?php

namespace App\Models;

class Actividades extends DBAbstractModel
{
    private $id;
    private $centro_civico_id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_final;
    private $horario;
    private $plazas;

    // Modelo singleton
    private static $instancia;
    public static function getInstancia()
    {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }

    // Método para obtener una actividad o todas las actividades
    public function get($id = null, $filters = [])
    {
        if ($id !== null) {
            // Consulta para obtener una actividad específica
            $this->query = "SELECT * FROM actividades WHERE id = :id";
            $this->parametros = [":id" => (int)$id];
        } elseif (!empty($filters)) {
            // Construcción dinámica de la consulta con filtros
            $this->query = "SELECT * FROM actividades WHERE 1=1";
    
            if (!empty($filters['centro_civico_id'])) {
                $this->query .= " AND centro_civico_id = :centro_civico_id";
                $this->parametros[':centro_civico_id'] = (int)$filters['centro_civico_id'];
            }
    
            if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_final'])) {
                $this->query .= " AND fecha_inicio >= :fecha_inicio AND fecha_final <= :fecha_final";
                $this->parametros[':fecha_inicio'] = $filters['fecha_inicio'];
                $this->parametros[':fecha_final'] = $filters['fecha_final'];
            }
    
            if (!empty($filters['nombre'])) {
                $this->query .= " AND nombre LIKE :nombre";
                $this->parametros[':nombre'] = "%" . $filters['nombre'] . "%";
            }
    
            if (!empty($filters['descripcion'])) {
                $this->query .= " AND descripcion LIKE :descripcion";
                $this->parametros[':descripcion'] = "%" . $filters['descripcion'] . "%";
            }
    
            if (!empty($filters['plazas'])) {
                $this->query .= " AND plazas = :plazas";
                $this->parametros[':plazas'] = (int)$filters['plazas'];
            }
        } else {
            // Consulta para obtener todas las actividades
            $this->query = "SELECT * FROM actividades";
            $this->parametros = [];
        }
    
        $this->get_results_from_query();
    
        if (!empty($this->rows)) {
            return $this->rows;
        } else {
            $this->mensaje = "No se encontraron resultados";
            return null;
        }
    }
    

    // Método para insertar una nueva actividad
    public function set($data = [])
    {
        // Validar que los datos requeridos estén presentes
        if (empty($data['centro_civico_id']) || empty($data['nombre']) || empty($data['descripcion']) || empty($data['fecha_inicio']) || empty($data['fecha_final']) || empty($data['horario']) || empty($data['plazas'])) {
            $this->mensaje = "Faltan datos para registrar la actividad";
            return false;
        }

        $this->query = "INSERT INTO actividades (centro_civico_id, nombre, descripcion, fecha_inicio, fecha_final, horario, plazas)
                        VALUES (:centro_civico_id, :nombre, :descripcion, :fecha_inicio, :fecha_final, :horario, :plazas)";
        $this->parametros = [
            ":centro_civico_id" => $data['centro_civico_id'],
            ":nombre" => $data['nombre'],
            ":descripcion" => $data['descripcion'],
            ":fecha_inicio" => $data['fecha_inicio'],
            ":fecha_final" => $data['fecha_final'],
            ":horario" => $data['horario'],
            ":plazas" => $data['plazas']
        ];

        $this->get_results_from_query();
        $this->mensaje = "Actividad registrada exitosamente";
        return true;
    }

    // Método para actualizar una actividad
    public function edit($data = [])
    {
        if (empty($data['id']) || empty($data['nombre']) || empty($data['descripcion']) || empty($data['fecha_inicio']) || empty($data['fecha_final']) || empty($data['horario']) || empty($data['plazas'])) {
            $this->mensaje = "Faltan datos para actualizar la actividad";
            return false;
        }

        $this->query = "UPDATE actividades
                        SET nombre = :nombre, descripcion = :descripcion, fecha_inicio = :fecha_inicio, fecha_final = :fecha_final, horario = :horario, plazas = :plazas
                        WHERE id = :id";
        $this->parametros = [
            ":id" => $data['id'],
            ":nombre" => $data['nombre'],
            ":descripcion" => $data['descripcion'],
            ":fecha_inicio" => $data['fecha_inicio'],
            ":fecha_final" => $data['fecha_final'],
            ":horario" => $data['horario'],
            ":plazas" => $data['plazas']
        ];

        $this->get_results_from_query();
        $this->mensaje = "Actividad actualizada exitosamente";
        return true;
    }

    // Método para eliminar una actividad
    public function delete($id = '')
    {
        if (empty($id)) {
            $this->mensaje = "Falta el ID de la actividad para eliminar";
            return false;
        }

        $this->query = "DELETE FROM actividades WHERE id = :id";
        $this->parametros = [":id" => $id];

        $this->get_results_from_query();
        $this->mensaje = "Actividad eliminada exitosamente";
        return true;
    }
}
