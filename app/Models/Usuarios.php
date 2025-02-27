<?php

namespace App\Models;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Usuarios extends DBAbstractModel
{

    // Creacion de atributos
    private $id;
    private $nombre;
    private $email;
    private $password;

    // Creacion de los setters
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

    public function getMensaje()
    {
        return $this->mensaje;
    }

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

    // Metodos abstractos de la clase padre DBAbstractModel
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }


    // Creacion del set
    public function set($sh_data = array())
    {
        // Validar que los datos requeridos estén presentes
        if (empty($sh_data["nombre"]) || empty($sh_data["email"]) || empty($sh_data["password"])) {
            $this->mensaje = "Faltan datos para registrar el usuario";
            return false;
        }
    
        // Verificar si el email ya está registrado
        $this->query = "SELECT id FROM usuarios WHERE email = :email";
        $this->parametros = [":email" => $sh_data["email"]];
        $this->get_results_from_query();
    
        if (!empty($this->rows)) {
            $this->mensaje = "El email ya está registrado";
            return false;
        }
    
        // Insertar nuevo usuario
        $this->query = "INSERT INTO usuarios (nombre, email, password) 
                        VALUES (:nombre, :email, :password)";
        $this->parametros = [
            ":nombre" => $sh_data["nombre"],
            ":email" => $sh_data["email"],
            ":password" => $sh_data["password"], // Se mantiene sin encriptar por tu preferencia
        ];
        $this->get_results_from_query();
    
        // Verificamos si se ha añadido correctamente
        if ($this->affected_rows > 0) {
            $this->mensaje = "Usuario registrado exitosamente";
            return true;
        } else {
            $this->mensaje = "Error al registrar el usuario";
            return false;
        }
    }

    // Creacion del get
    public function get($id = null)
    {
        if ($id != null) {
            $this->query = "SELECT * FROM usuarios WHERE id = :id";
            $this->parametros[":id"] = $id;
            $this->get_results_from_query();

            if (!empty($this->rows)) {
                $this->mensaje = "Usuario encontrado";
                return $this->rows[0]; // Devolver el usuario encontrado
            } else {
                $this->mensaje = "Usuario no encontrado";
                return null;
            }
        }

    $this->mensaje = "Se requiere un ID de usuario";
    return null;    
}

    // Creacion del edit
    public function edit($sh_data = array())
    {
        if (empty($sh_data['id']) || empty($sh_data['nombre']) || empty($sh_data['password']) || empty($sh_data['email'])) {
            $this->mensaje = 'Faltan datos para la actualización';
            return;
        }

        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        // Definir la consulta SQL para actualizar un usuario
        $this->query = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :password WHERE id = :id";

        // Mapear los valores del array a los parámetros de la consulta
        $this->parametros[":nombre"] = $sh_data["nombre"];
        $this->parametros[":email"] = $sh_data["email"];
        $this->parametros[":password"] = $sh_data["password"];
        $this->parametros[":id"] = $sh_data["id"];

        // Ejecutar la consulta
        $this->get_results_from_query();

        // Establecer el mensaje de éxito
        $this->mensaje = "Usuario actualizado exitosamente";
    }


    // Creacion del delete
    public function delete($id = null)
    {
        if (!$id) {
            $this->mensaje = "ID de usuario no proporcionado";
            return false;
        }
    
        $this->query = "DELETE FROM usuarios WHERE id = :id";
        $this->parametros[":id"] = $id;
        $this->get_results_from_query();
    
        // Verificar si se eliminó al menos una fila
        if ($this->affected_rows > 0) {
            $this->mensaje = "Usuario eliminado exitosamente";
            return true;
        } else {
            $this->mensaje = "Usuario no encontrado";
            return false;
        }
    }
    


    // Creacion del login, con JWT
    public function login($email, $password) {
        // Buscar usuario por email
        $this->query = "SELECT * FROM usuarios WHERE email = :email";
        $this->parametros[":email"] = $email;
        $this->get_results_from_query();
    
        // Verificar si el usuario existe
        if (!empty($this->rows)) {
            $usuario = $this->rows[0];
    
            // Comparación DIRECTA de contraseñas (sin hashing)
            if ($password === $usuario['password']) {
                $this->mensaje = "Inicio de sesión exitoso";
    
                // Obtener clave del .env (asegúrate de cargarla en el bootstrap)
                $secret_key = KEY;
    
                // Generar token JWT
                $payload = [
                    'sub' => $usuario['id'],
                    'name' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'iat' => time(),
                    'exp' => time() + (60 * 60) // Token válido por 1 hora
                ];
    
                return JWT::encode($payload, $secret_key, 'HS256');
            } else {
                $this->mensaje = "Contraseña incorrecta";
                return null;
            }
        }
    
        $this->mensaje = "Usuario no encontrado";
        return null;
    }
    
    public function refrescarToken($token) {
        try {
            // Obtener la clave secreta desde el bootstrap.php
            $secret_key = KEY;
    
            // Decodificar el token para verificar su validez
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
            // Crear un nuevo payload con los mismos datos, pero con una nueva expiración
            $new_payload = [
                'sub' => $decoded->sub,
                'name' => $decoded->name,
                'email' => $decoded->email,
                'iat' => time(),
                'exp' => time() + (60 * 60) // Nuevo token válido por 1 hora
            ];
    
            $this->mensaje = "Token renovado exitosamente";
    
            // Retornar el nuevo token JWT
            return JWT::encode($new_payload, $secret_key, 'HS256');
        } catch (\Exception $e) {
            // Si hay un error (por ejemplo, el token ha expirado o es inválido)
            $this->mensaje = "Token inválido o expirado";
            return null;
        }
    }
    

}