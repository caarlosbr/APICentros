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
        // Asignar dinámicamente los valores del array a las variables de la clase
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }
    
        // Consulta SQL para insertar un nuevo centro cívico
        $this->query = "INSERT INTO centros_civicos (nombre, direccion, telefono, horario, foto, created_at) 
                        VALUES (:nombre, :direccion, :telefono, :horario, :foto, :created_at)";
    
        // Asignar los valores a los parámetros de la consulta
        $this->parametros['nombre'] = $sh_data['nombre'];
        $this->parametros['direccion'] = $sh_data['direccion'];
        $this->parametros['telefono'] = $sh_data['telefono'];
        $this->parametros['horario'] = $sh_data['horario'];
        $this->parametros['foto'] = $sh_data['foto'] ?? null; // Campo opcional
        $this->parametros['created_at'] = date('Y-m-d H:i:s'); // Marca de tiempo para la creación
    
        // Ejecutar la consulta
        $this->get_results_from_query();
    
        // Mensaje de éxito
        $this->mensaje = "Centro cívico registrado exitosamente";
    }
    

    // Método para actualizar un centro
    public function edit($data = [])
    {
        // Validar que todos los datos requeridos estén presentes
        if (empty($data['id']) || empty($data['nombre']) || empty($data['direccion']) || empty($data['telefono']) || empty($data['horario'])) {
            $this->mensaje = 'Faltan datos para la actualización del centro cívico';
            return false;
        }
    
        // Consulta para actualizar un centro cívico
        $this->query = "UPDATE centros_civicos 
                        SET nombre = :nombre, direccion = :direccion, telefono = :telefono, horario = :horario, foto = :foto 
                        WHERE id = :id";
    
        // Mapear los valores a los parámetros
        $this->parametros['id'] = $data['id'];
        $this->parametros['nombre'] = $data['nombre'];
        $this->parametros['direccion'] = $data['direccion'];
        $this->parametros['telefono'] = $data['telefono'];
        $this->parametros['horario'] = $data['horario'];
        $this->parametros['foto'] = $data['foto'] ?? null;
    
        // Ejecutar la consulta
        $this->get_results_from_query();
    
        // Mensaje de éxito
        $this->mensaje = 'Centro cívico actualizado correctamente';
        return true;
    }
    

    // Método para eliminar un centro
    public function delete($id = ''){
        $this->query = "DELETE FROM centros_civicos WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->get_results_from_query();
    }
}
