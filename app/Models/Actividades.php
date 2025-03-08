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
        // Empezamos asumiendo que no hay parámetros para la consulta.
        $this->parametros = [];
    
        // 1) Si se proporciona un ID, buscamos solo esa actividad.
        if ($id !== null) {
            // Construimos la consulta para buscar la actividad por su ID.
            $this->query = "SELECT * FROM actividades WHERE id = :id";
    
            // Asignamos el valor de :id en el array de parámetros.
            // (int)$id fuerza a convertir $id a entero.
            $this->parametros = [":id" => (int)$id];
    
        } else {
            // 2) Si no se pasa un ID, podemos aplicar filtros o devolver todas las actividades.
    
            // Empezamos la consulta con 'WHERE 1=1' para facilitar la concatenación de 'AND ...'.
            $baseQuery = "SELECT * FROM actividades WHERE 1=1";
    
            // $conditions es un array que iremos llenando con cada filtro que encontremos.
            $conditions = [];
    
            // Si el array $filters contiene 'centro_civico_id', añadimos esa condición.
            if (!empty($filters['centro_civico_id'])) {
                $conditions[] = "centro_civico_id = :centro_civico_id";
                $this->parametros[':centro_civico_id'] = (int)$filters['centro_civico_id'];
            }
    
            // Si hay un rango de fechas, añadimos las condiciones fecha_inicio y fecha_final.
            if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_final'])) {
                $conditions[] = "fecha_inicio >= :fecha_inicio AND fecha_final <= :fecha_final";
                $this->parametros[':fecha_inicio'] = $filters['fecha_inicio'];
                $this->parametros[':fecha_final']  = $filters['fecha_final'];
            }
    
            // Si existe un 'nombre' en los filtros, lo usamos con LIKE para coincidencias parciales.
            if (!empty($filters['nombre'])) {
                $conditions[] = "nombre LIKE :nombre";
                $this->parametros[':nombre'] = "%" . $filters['nombre'] . "%";
            }
    
            // Si hay 'descripcion', también la filtramos con LIKE.
            if (!empty($filters['descripcion'])) {
                $conditions[] = "descripcion LIKE :descripcion";
                $this->parametros[':descripcion'] = "%" . $filters['descripcion'] . "%";
            }
    
            // Si el usuario pasó 'plazas', añadimos la condición exacta para plazas.
            if (!empty($filters['plazas'])) {
                $conditions[] = "plazas = :plazas";
                $this->parametros[':plazas'] = (int)$filters['plazas'];
            }
    
            // Unimos la consulta base con las condiciones que haya.
            // Si $conditions no está vacío, concatenamos ' AND ...'.
            $this->query = $baseQuery . (count($conditions) > 0 ? " AND " . implode(" AND ", $conditions) : "");
        }
    
        // 3) Ejecutamos la consulta final y guardamos los resultados en $this->rows.
        $this->get_results_from_query();
    
        // 4) Retornamos los resultados si hay filas; de lo contrario, guardamos un mensaje y devolvemos null.
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
    }

    // Método para actualizar una actividad
    public function edit($data = [])
    {
    }

    // Método para eliminar una actividad
    public function delete($id = '')
    {
    }
}
