<?php

session_start(['read_and_close'=>true]);
$data = json_decode(file_get_contents('php://input'), true);

if (!(isset($_SESSION['isAdmin']) && isset($data['ID'])))
    require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';


if ((int)$data['ID']) {
    // Primero eliminar todos los artículos relacionados con el grupo
    $db->query("DELETE FROM `pd_articulos` WHERE `grupoID` = ".(int)$data['ID']);
    $articulos_afectados = $db -> affected_rows();
    if ($articulos_afectados >= 0) { // Verificamos si hubo éxito eliminando los artículos o si no había artículos relacionados
        $db->query("DELETE FROM `pd_grupos` WHERE `ID` = ".(int)$data['ID']);
        echo "Se eliminaron". $db->affected_rows()."articulos dentro del grupo";  // Devolver el número de filas afectadas al eliminar el grupo
    } else {
        echo 0; // Error al eliminar artículos relacionados
    }
} else {
    echo 0; // No se proporcionó un ID válido
}

?>

