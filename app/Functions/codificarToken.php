<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('decodificarToken')) {
    function decodificarToken() {
        // Verificar si el token JWT está presente
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return null;
        }
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']; // Obtener el encabezado de autorización
        $arr = explode(" ", $authHeader); // Separar el encabezado en un arreglo
        $jwt = $arr[1]; // Obtener el token JWT
        // Verificar si el token JWT está presente
        if (!$jwt) {
            return null;
        }

        // Decodificar el token JWT
        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256')); // Decodificar el token JWT
            // Imprimir el objeto decodificado para ver su estructura
/*             var_dump($decoded); // DEBUG          
 */            // Ajustar el acceso a las propiedades según la estructura real del objeto decodificado
            $idUser = $decoded->sub; // Asumiendo que la ID del usuario está en el campo 'sub'
            var_dump($idUser); // DEBUG
            return $idUser; // Retornar la ID del usuario
        } catch (Exception $e) { // Capturar cualquier excepción
            return null;
        }
    }
}