<?php

namespace App\Models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Usuarios extends DBAbstractModel
{
    // Atributos para representar las columnas de la tabla de usuarios
    private $id;
    private $nombre;
    private $email;
    private $password;

    // Setters para asignar valores a los atributos
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    // Método para obtener mensajes de estado o error
    public function getMensaje()
    {
        return $this->mensaje;
    }

    // Implementación del patrón Singleton para asegurar una única instancia
    private static $instancia;
    public static function getInstancia()
    {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    // Evita la clonación de la instancia
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }

    // Método para registrar un nuevo usuario (CREATE)
    public function set($sh_data = array())
    {
        // Verifica que se proporcionen todos los datos requeridos
/*         if (empty($sh_data["nombre"]) || empty($sh_data["email"]) || empty($sh_data["password"])) {
            $this->mensaje = "Faltan datos para registrar el usuario";
            return false;
        } */

        
 /*        $this->query = "SELECT id FROM usuarios WHERE email = :email"; // 
        $this->parametros = [":email" => $sh_data["email"]];
        $this->get_results_from_query(); */
    
        // Si se encuentra un registro, retorna un mensaje de error
        if (!empty($this->rows)) { // si el email ya existe
            $this->mensaje = "El email ya está registrado";
            return false;
        }
    
        // Inserta el nuevo usuario en la base de datos
        $this->query = "INSERT INTO usuarios (nombre, email, password) 
                        VALUES (:nombre, :email, :password)";
        $this->parametros = [
            ":nombre" => $sh_data["nombre"],
            ":email" => $sh_data["email"],
            ":password" => $sh_data["password"], 
        ];
        $this->get_results_from_query();
    
        // Verifica si la inserción fue exitosa
        if ($this->affected_rows > 0) {
            $this->mensaje = "Usuario registrado exitosamente";
            return true;
        } else {
            $this->mensaje = "Error al registrar el usuario";
            return false;
        }
    }

    // Método para obtener la información de un usuario (READ)
    public function get($id = null)
    {
        // var_dump("Entrando a get");

        // Verifica si se proporciona un ID
        if ($id != null) {
            // Consulta para obtener al usuario por su ID
            $this->query = "SELECT * FROM usuarios WHERE id = :id";
            $this->parametros[":id"] = $id;
            $this->get_results_from_query();

            // Si se encuentra el usuario, retorna sus datos
            if (!empty($this->rows)) {
                $this->mensaje = "Usuario encontrado";
                /* var_dump($this->rows[0]); */
                return $this->rows[0]; // esto los datos del usuario
            } else {
                $this->mensaje = "Usuario no encontrado";
                return null;
            }
        }
        // Si no se proporciona un ID, se retorna un mensaje de error
        $this->mensaje = "Se requiere un ID de usuario";
        return null;    
    }

    // Método para actualizar la información de un usuario (UPDATE)
    public function edit($sh_data = array())
    {
        // Verifica que se proporcionen todos los datos necesarios para la actualización
/*         if (empty($sh_data['id']) || empty($sh_data['nombre']) || empty($sh_data['password']) || empty($sh_data['email'])) {
            $this->mensaje = 'Faltan datos para la actualización';
            return;
        } */

        // Se mapean los datos (la línea foreach asigna cada dato a una variable con su mismo nombre)
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        // Define la consulta SQL para actualizar el usuario
        $this->query = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :password WHERE id = :id";

        // Mapea los parámetros para la consulta
        $this->parametros[":nombre"] = $sh_data["nombre"];
        $this->parametros[":email"] = $sh_data["email"];
        $this->parametros[":password"] = $sh_data["password"];
        $this->parametros[":id"] = $sh_data["id"];

        // Ejecuta la consulta
        $this->get_results_from_query();

        // Mensaje de éxito
        $this->mensaje = "Usuario actualizado exitosamente";
    }

    // Método para eliminar un usuario (DELETE)
    public function delete($id = null)
    {
        // Verifica que se haya proporcionado un ID
        if (!$id) {
            $this->mensaje = "ID de usuario no proporcionado";
            return false;
        }
    
        // Consulta SQL para eliminar al usuario por su ID
        $this->query = "DELETE FROM usuarios WHERE id = :id";
        $this->parametros[":id"] = $id;
        $this->get_results_from_query();
    
        // Verifica si se eliminó algún registro
        if ($this->affected_rows > 0) {
            $this->mensaje = "Usuario eliminado exitosamente";
            return true;
        } else {
            $this->mensaje = "Usuario no encontrado";
            return false;
        }
    }
    
    // Método para el inicio de sesión (login) usando JWT
    public function login($email, $password) {
        // Busca al usuario por email
        $this->query = "SELECT * FROM usuarios WHERE email = :email";
        $this->parametros[":email"] = $email;
        $this->get_results_from_query();
    
        // Si el usuario es encontrado
        if (!empty($this->rows)) {
            $usuario = $this->rows[0];
    
            // Compara directamente las contraseñas 
            if ($password === $usuario['password']) {
                $this->mensaje = "Inicio de sesión exitoso";
    
                // Se obtiene la clave secreta (definida en una variable de entorno, por ejemplo)
                $secret_key = KEY;
    
                // Define el payload (es el contenido del token) del token JWT con información del usuario y tiempos de emisión y expiración
                $payload = [
                    'sub' => $usuario['id'], // ID del usuario
                    'name' => $usuario['nombre'], // Nombre del usuario
                    'email' => $usuario['email'], // Email del usuario
                    'iat' => time(), // Tiempo de emisión del token
                    'exp' => time() + (60 * 60) // Token válido por 1 hora
                ];
    
                // Retorna el token JWT codificado
                return JWT::encode($payload, $secret_key, 'HS256'); // Codifica el token, HS256 es el algoritmo de encriptación
            } else {
                $this->mensaje = "Contraseña incorrecta";
                return null;
            }
        }
    
        $this->mensaje = "Usuario no encontrado"; // Si $this->rows está vacío
        return null;
    }
    
    // Método para refrescar el token JWT
    public function refrescarToken($token) {
        try {
            // Se obtiene la clave secreta
            $secret_key = KEY;
    
            // Decodifica el token para validar su contenido y asegurarse de que no esté alterado
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
            // Crea un nuevo payload (es el contenido del token) con la misma información, pero con un nuevo tiempo de expiración
            $new_payload = [
                'sub' => $decoded->sub,
                'name' => $decoded->name,
                'email' => $decoded->email,
                'iat' => time(),
                'exp' => time() + (60 * 60) // Nuevo token válido por 1 hora
            ];
    
            $this->mensaje = "Token renovado exitosamente";
    
            // Retorna el nuevo token codificado
            return JWT::encode($new_payload, $secret_key, 'HS256'); // Codifica el nuevo token
        } catch (\Exception $e) {
            // En caso de error (token expirado o inválido), se retorna un mensaje de error
            $this->mensaje = "Token inválido o expirado";
            return null;
        }
    }
}
