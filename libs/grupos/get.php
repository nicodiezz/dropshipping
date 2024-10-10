<?php

session_start(['read_and_close' => true]);
$data = json_decode(file_get_contents('php://input'), true);

require '../db.php';

// Verificar si el parámetro 'grupoID' esta
if (!isset($data['grupoID'])) {
    http_response_code(400); // Código 400 Bad Request
    die(json_encode(['error' => 'Falta el parámetro grupoID']));
}

// Convertir el grupoID a entero para evitar problemas de seguridad (inyección SQL)
$grupoID = (int)$data['grupoID'];


$consulta = "SELECT * FROM `pd_grupos` WHERE `ID` = $grupoID";
$resultado = $db->query($consulta);

// Si la preparación falla
if (!$resultado) {
    http_response_code(500);
    die(json_encode(['error' => 'Error al preparar la consulta']));
}

if ($resultado->num_rows > 0) {
    die(json_encode($resultado->fetch_assoc()));
} else {
    http_response_code(404);
    die(json_encode(['error' => 'Grupo no encontrado']));
}