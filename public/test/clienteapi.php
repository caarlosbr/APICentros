<?php
require "../../bootstrap.php";

// Dirección de api
$issuer = 'http://contactos.local';

// Credenciales
$credenciales = [
    'usuario' => 'admin',
    'password' => 'admin'
];

// Obtenemos el token de acceso.
$token = obtainToken($credenciales, $issuer);
echo $token;

// Tests

//Recuperamos todos los contactos
getAllContactos($token);

//Recuperamos el contacto cuyo id es 1
getContacto($token, 1);

//Añadimos un nuevo contacto
$contacto = [
    "nombre" => "Laura",
    "telefono" => "12345678",
    "email" => "laura@gmail.com"
];
addContacto($token, $contacto);

//Borrando contacto id 6
delContacto($token, 6);

//Modificamos un contacto
$datos = [
    "id" => 6,
    "nombre" => "Laura Luque",
    "telefono" => "12345678",
    "email" => "laura@gmail.com"
];

editContacto($token, $datos);

function obtainToken($datos, $issuer) {
    // Comprobamos si disponemos del token en almacenamiento local

    // cargamos 
    $uri = $issuer . '/login';

    // peticion curl
    // inicio
    $ch = curl_init();

    // parametrizacion
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // peticion
    $response = curl_exec($ch);

    $response = json_decode($response, true);
    if (!isset($response['jwt'])) {
        exit('failed, exinting');
    }

    echo "Token OK <br/>";
    // Almacenamiento local del token

    return $response['jwt'];
};

function getAllContactos($token) {
    echo "<br/>Obteniendo todos los contacto...<br/>";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://contactos.local/contactos/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response;
};

function getContacto($token, $id) {
    echo "<br/>Obteniendo contacto por id: $id...<br/>";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://contactos.local/contactos/". $id);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response;
};

function addContacto($token, $datos) {
    echo "<br/>Nuevo contacto...<br/>";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://contactos.local/contactos/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response;
}

function editContacto($token, $datos) {
    echo "<br/>Nuevo contacto...<br/>";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://contactos.local/contactos/" . $datos['id']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $response = curl_exec($ch);
    echo $response;
}

function delContacto($token, $id) {
    echo "<br/>Borrando contacto con id: $id...<br/>";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://contactos.local/contactos/" . $id);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response;
}