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
    public function get($usuario_id = "") {
        if (empty($usuario_id)) {
            $this->mensaje = "Falta el ID del usuario";
            return null;
        }

        // var_dump($usuario_id);

        $this->query = "SELECT * FROM reservas WHERE usuario_id = :usuario_id";
        $this->parametros = [":usuario_id" => (int)$usuario_id];
        $this->get_results_from_query();

        if (!empty($this->rows)) {
            $this->mensaje = "Reservas encontradas";
            return $this->rows;
        } else {
            $this->mensaje = "No se encontraron reservas";
            return null;
        }
        
    }
    

    // Crear una nueva reserva
    public function set($input = array()) {
        $this->query = "INSERT INTO reservas (
            usuario_id, nombre_solicitante, telefono, correo_electronico, instalacion_id, fecha_inicio, fecha_final, estado
        ) VALUES (
            :usuario_id, :nombre_solicitante, :telefono, :correo_electronico, :instalacion_id, :fecha_inicio, :fecha_final, :estado
        )";
    
        $this->parametros = [
            ':usuario_id' => $input['usuario_id'],
            ':nombre_solicitante' => $input['nombre_solicitante'],
            ':telefono' => $input['telefono'],
            ':correo_electronico' => $input['correo_electronico'],
            ':instalacion_id' => $input['instalacion_id'],
            ':fecha_inicio' => $input['fecha_inicio'],
            ':fecha_final' => $input['fecha_final'],
            ':estado' => $input['estado']
        ];
    
        // Depuración: Imprimir la consulta y los parámetros
/*         var_dump($this->query);
        var_dump($this->parametros); */
    
        $this->get_results_from_query();
        $this->mensaje = 'Reserva creada';
        return $this->rows;
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

    public function getById($id) {
        if (empty($id)) {
            return null;
        }
    
        $this->query = "SELECT * FROM reservas WHERE id = :id";
        $this->parametros = [":id" => (int)$id];
        $this->get_results_from_query();
    
        return $this->rows[0] ?? null;
    }
    
}
