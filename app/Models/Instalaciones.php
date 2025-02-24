<?php

namespace App\Models;

class Instalaciones extends DBAbstractModel
{
    private $id;
    private $nombre;
    private $centro_id; // Relación con el centro cívico
    private $tipo;
    private $capacidad;

    // Singleton
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

    // Obtener una instalación por ID o todas las instalaciones
    public function get($id = null, $filters = [])
    {
        if ($id !== null) {
            // Si se pasa un ID, buscar una instalación específica
            $this->query = "SELECT * FROM instalaciones WHERE id = :id";
            $this->parametros = [":id" => (int)$id];
        } elseif (!empty($filters['centro_civico_id'])) {
            // Si se pasa el centro_civico_id, buscar instalaciones de ese centro
            $this->query = "SELECT * FROM instalaciones WHERE centro_civico_id = :centro_civico_id";
            $this->parametros = [":centro_civico_id" => (int)$filters['centro_civico_id']];
        } else {
            // Si no hay filtros, devolver todas las instalaciones
            $this->query = "SELECT * FROM instalaciones";
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
    
    

    // Crear una nueva instalación
    public function set($data = [])
    {
        if (empty($data['nombre']) || empty($data['centro_id']) || empty($data['tipo']) || empty($data['capacidad'])) {
            $this->mensaje = "Faltan datos para registrar la instalación";
            return false;
        }

        $this->query = "INSERT INTO instalaciones (nombre, centro_id, tipo, capacidad, created_at) 
                        VALUES (:nombre, :centro_id, :tipo, :capacidad, :created_at)";
        $this->parametros = [
            ":nombre" => $data['nombre'],
            ":centro_id" => (int)$data['centro_id'],
            ":tipo" => $data['tipo'],
            ":capacidad" => $data['capacidad'],
            ":created_at" => date('Y-m-d H:i:s')
        ];

        $this->get_results_from_query();
        $this->mensaje = "Instalación registrada exitosamente";
        return true;
    }

    // Actualizar una instalación
    public function edit($data = [])
    {
        if (empty($data['id']) || empty($data['nombre']) || empty($data['centro_id']) || empty($data['tipo']) || empty($data['capacidad'])) {
            $this->mensaje = 'Faltan datos para la actualización de la instalación';
            return false;
        }

        $this->query = "UPDATE instalaciones 
                        SET nombre = :nombre, centro_id = :centro_id, tipo = :tipo, capacidad = :capacidad 
                        WHERE id = :id";
        $this->parametros = [
            ":id" => (int)$data['id'],
            ":nombre" => $data['nombre'],
            ":centro_id" => (int)$data['centro_id'],
            ":tipo" => $data['tipo'],
            ":capacidad" => $data['capacidad']
        ];

        $this->get_results_from_query();
        $this->mensaje = "Instalación actualizada exitosamente";
        return true;
    }

    // Eliminar una instalación
    public function delete($id = '')
    {
        if (empty($id)) {
            $this->mensaje = "Falta el ID de la instalación para eliminar";
            return false;
        }

        $this->query = "DELETE FROM instalaciones WHERE id = :id";
        $this->parametros[":id"] = (int)$id;

        $this->get_results_from_query();
        $this->mensaje = "Instalación eliminada exitosamente";
        return true;
    }
}
