<?php

namespace App\Models;

class CentrosCivicos extends DBAbstractModel
{
    private $id;
    private $nombre;
    private $direccion;
    private $telefono;
    private $horario;
    private $foto;

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

    // Método para obtener un centro por ID o todos los centros
    public function get($id = '')
    {
        if ($id != '') {
            // Consulta para obtener un centro cívico por su ID
            $this->query = "SELECT * FROM centros_civicos WHERE id = :id";
            $this->parametros[":id"] = (int)$id; // Asegúrate de convertir el ID a entero
            $this->get_results_from_query();
        } else {
            // Consulta para obtener todos los centros cívicos
            $this->query = "SELECT * FROM centros_civicos";
            $this->parametros = [];
            $this->get_results_from_query();
        }
    
        if (!empty($this->rows)) {
            if ($id != '') {
                foreach ($this->rows[0] as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }
                $this->mensaje = "Centro cívico encontrado";
                return $this->rows[0];
            } else {
                $this->mensaje = "Centros cívicos encontrados";
                return $this->rows;
            }
        } else {
            $this->mensaje = $id ? "Centro cívico no encontrado" : "No se encontraron centros cívicos";
            return null;
        }
    }
    
    
    

    // Método para insertar un nuevo centro
    public function set($sh_data = [])
    {
    }
    

    // Método para actualizar un centro
    public function edit($data = [])
    {
    }
    

    // Método para eliminar un centro
    public function delete($id = ''){
    }
}
