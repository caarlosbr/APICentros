<?php
namespace App\Models;
abstract class DBAbstractModel
{
    private static $db_host = DBHOST;
    private static $db_user = DBUSER;
    private static $db_pass = DBPASS;
    private static $db_name = DBNAME;
    private static $db_port = DBPORT;

    protected $mensaje = "";
    protected $conn; //Manejador de la BD
    //Manejo básico para consultas.
    protected $query; //consulta
    protected $parametros = array(); //parámetros de entrada
    protected $rows = array(); //array con los datos de salida
    protected $affected_rows; // Numeros de filas afectadas

    //Metodos abstractos para implementar en los diferentes módulos. CRUD
    abstract protected function get(); //Read
    abstract protected function set(); //Create
    abstract protected function edit(); //Update
    abstract protected function delete(); //Delete
    #Crear conexión a la base de datos.
    protected function open_connection()
    {
        $dsn = 'mysql:host=' . self::$db_host . ';' . 'dbname=' . self::$db_name . ';' . 'port=' . self::$db_port;
        try {
            $this->conn = new \PDO(
                $dsn,
                self::$db_user,
                self::$db_pass,
                //Se puede incluir BUFFERED_QUERY
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );

            return $this->conn;
            
        } catch (\PDOException $e) {
            printf("Conexión fallida: %s\n", $e->getMessage());
            exit();
        }
    }
    #Método que devuelve el último id introducido.
    public function lastInsert()
    {
        return $this->conn->lastInsertId();
    }

    # Desconectar la base de datos
    private function close_connection()
    {
        $this->conn = null;
    }

    protected function get_results_from_query()
    {
        $this->open_connection();
    
        if (($_stmt = $this->conn->prepare($this->query))) {
            if (preg_match_all('/(:\w+)/', $this->query, $_named, PREG_PATTERN_ORDER)) {
                $_named = array_pop($_named);
                foreach ($_named as $_param) {
                    // Enlazar directamente usando la clave completa, como ":id"
                    if (isset($this->parametros[$_param])) {
                        $_stmt->bindValue($_param, $this->parametros[$_param]);
                    } 
                }
            }
        }
    
        try {
            if (!$_stmt->execute()) {
                var_dump($_stmt->errorInfo()); // Depuración de errores en la consulta
            }
    
            $this->rows = $_stmt->fetchAll(\PDO::FETCH_ASSOC);
            $_stmt->closeCursor();
        } catch (\PDOException $e) {
            var_dump($e->getMessage()); // Depuración de errores de PDO
        }
        $this->affected_rows = $_stmt->rowCount();
    }
    
    
}

