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
    }

    // Actualizar una instalación
    public function edit($data = [])
    {
    }

    // Eliminar una instalación
    public function delete($id = '')
    {
    }
}
