<?php

session_start(['read_and_close' => true]);
$data = json_decode(file_get_contents('php://input'), true);


if (!(isset($_SESSION['isAdmin']) && isset($data['ID']))) {
    require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
    exit;
}

require '../db.php';

if ((int)$data['ID']) {
    // Elimino la relacion de los vendedores para poder eliminar el grupo
    $db->query("DELETE FROM `pd_vendedores_grupos` WHERE `grupoID` = " . (int)$data['ID']);
    // Solo eliminar el grupo, sin eliminar los artículos relacionados
    $db->query("DELETE FROM `pd_grupos` WHERE `ID` = ".(int)$data['ID']);
    $grupos_afectados = $db->affected_rows();
    
    if ($grupos_afectados > 0) {
        echo "Grupo eliminado correctamente.";
    } else {
        echo 0; 
    }
} else {
    echo 0; // No se proporcionó un ID válido
}

?>
