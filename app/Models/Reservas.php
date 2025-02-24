<?php

namespace App\Models;

class Reservas extends DBAbstractModel {
    private $id;
    private $usuario_id;
    private $actividad_id;
    private $fecha_reserva;

    // Singleton
    private static $instancia;
    public static function getInstancia() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    public function __clone() {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }

    // Obtener una reserva por usuario_id o todas las reservas
    public function get($usuario_id = '') {
        if ($usuario_id != '') {
            // Consulta para obtener reservas de un usuario específico
            $this->query = "SELECT reservas.id, reservas.fecha_reserva, actividades.nombre AS actividad, actividades.descripcion
                            FROM reservas
                            INNER JOIN actividades ON reservas.actividad_id = actividades.id
                            WHERE reservas.usuario_id = :usuario_id";
            $this->parametros = [":usuario_id" => (int)$usuario_id];
            $this->get_results_from_query();
        } else {
            // Consulta para obtener todas las reservas
            $this->query = "SELECT * FROM reservas";
            $this->parametros = [];
            $this->get_results_from_query();
        }

        if (!empty($this->rows)) {
            if ($usuario_id != '') {
                $this->mensaje = "Reservas del usuario encontradas";
                return $this->rows;
            } else {
                $this->mensaje = "Reservas encontradas";
                return $this->rows;
            }
        } else {
            $this->mensaje = $usuario_id ? "No se encontraron reservas para este usuario" : "No se encontraron reservas";
            return null;
        }
    }

    // Crear una nueva reserva
    public function set($data = []) {
        if (empty($data['nombre_solicitante']) || empty($data['telefono']) || 
            empty($data['correo_electronico']) || empty($data['instalacion_id']) || 
            empty($data['fecha_inicio']) || empty($data['fecha_final']) || 
            empty($data['estado'])) {
            $this->mensaje = "Faltan datos para crear la reserva";
            return false;
        }
    
        // Preparar la consulta
        $this->query = "INSERT INTO reservas (nombre_solicitante, telefono, correo_electronico, instalacion_id, fecha_inicio, fecha_final, estado) 
                        VALUES (:nombre_solicitante, :telefono, :correo_electronico, :instalacion_id, :fecha_inicio, :fecha_final, :estado)";
    
        // Asignar valores a los parámetros
        $this->parametros = [
            ":nombre_solicitante" => $data['nombre_solicitante'],
            ":telefono" => $data['telefono'],
            ":correo_electronico" => $data['correo_electronico'],
            ":instalacion_id" => (int) $data['instalacion_id'],
            ":fecha_inicio" => $data['fecha_inicio'],
            ":fecha_final" => $data['fecha_final'],
            ":estado" => $data['estado']
        ];
    
        // Ejecutar la consulta
        $this->get_results_from_query();
    
        if ($this->conn->lastInsertId()) {
            $this->mensaje = "Reserva creada exitosamente";
            return true;
        } else {
            $this->mensaje = "Error al crear la reserva";
            return false;
        }
    }
    
    // Método de edición vacío, ya que no se utiliza para reservas
    public function edit() {}

    // Eliminar una reserva por ID
    public function delete($id = '') {
        if ($id == '') {
            $this->mensaje = "Falta el ID de la reserva para eliminar";
            return false;
        }

        $this->query = "DELETE FROM reservas WHERE id = :id";
        $this->parametros = [":id" => (int)$id];

        $this->get_results_from_query();

        $this->mensaje = "Reserva eliminada exitosamente";
        return true;
    }
}
