<?php
use App\Models\Contactos;


include("../app/core/config.php");
include("../app/models/Contactos.php");

$datos = array("nombre"=>"pepe","telefono"=>"123456789","email"=>"pepe_elshulo@gmail.com");
$datos2 = array("nombre"=>"paco","telefono"=>"123456789","email"=>"paco_elshulo@gmail.com");

$prueba1 = Contactos::getInstancia();
// $prueba1->set($datos2);
// $prueba1->delete("3");
// $prueba1->edit("1",$datos2);
// $result = $prueba1->get('1');
// if(empty($result)){
//     echo "no hay resultado";
// } else {
//     printf($result['nombre']);
// }


