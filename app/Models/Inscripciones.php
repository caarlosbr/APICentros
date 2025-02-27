<?php

namespace App\Models;

class Inscripciones extends DBAbstractModel {
    private $id;
    private $usuario_id;
    private $actividad_id;
    private $fecha_inscripcion;

    // Modelo singleton
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

    public function get(){}

    // Método para crear una nueva inscripción
    public function set($sh_data = []) {
        // Validar que todos los campos necesarios estén presentes
/*         if (empty($sh_data['nombre_solicitante']) || empty($sh_data['telefono']) || empty($sh_data['correo_electronico']) || empty($sh_data['actividad_id']) || empty($sh_data['estado'])) {
            $this->mensaje = "Faltan datos para crear la inscripción";
            return false;
        }
    
        // Asignar valores a las variables de la clase
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        } */
    
        // Consulta SQL para insertar una nueva inscripción
        $this->query = "INSERT INTO inscripciones (usuario_id, nombre_solicitante, telefono, correo_electronico, actividad_id, fecha_inscripcion, estado)
                        VALUES (:usuario_id, :nombre_solicitante, :telefono, :correo_electronico, :actividad_id, :fecha_inscripcion, :estado)";
    
        // Asignar parámetros a la consulta
        $this->parametros = [
            ':usuario_id' => $sh_data['usuario_id'],
            ':nombre_solicitante' => $sh_data['nombre_solicitante'],
            ':telefono' => $sh_data['telefono'],
            ':correo_electronico' => $sh_data['correo_electronico'],
            ':actividad_id' => $sh_data['actividad_id'],
            ':fecha_inscripcion' => date('Y-m-d H:i:s'), // Fecha actual si no se proporciona
            ':estado' => $sh_data['estado']
        ];

        // Ejecutar la consulta
        $this->get_results_from_query();
    
        // Mensaje de éxito
        $this->mensaje = "Inscripción creada exitosamente";
        return true;
    }
    

    public function edit(){}

    // Método para eliminar una inscripción
    public function delete($id = '') {
        if ($id !== '') {
            $this->query = "DELETE FROM inscripciones WHERE id = :id";
    
            // Asignar el valor al placeholder :id
            $this->parametros = [":id" => (int)$id];
    
            $this->get_results_from_query();
            $this->mensaje = "Inscripción eliminada exitosamente";
            return true;
        } else {
            $this->mensaje = "Falta el ID de la inscripción para eliminar";
            return false;
        }
    }
    
}
