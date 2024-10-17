<?php

session_start(['read_and_close' => true]);
$data = json_decode(file_get_contents('php://input'), true);

if (!(isset($_SESSION['isAdmin']) && isset($data['nombre']) && isset($data['comision']))) {
    echo 0;
    exit;
}

$nombre = trim($data['nombre']);
$comision = (float)$data['comision'];

// Validar que los campos no estén vacíos
if (empty($nombre) || $comision <= 0) {
    echo 0;
    exit;
}

require '../db.php';

if ($db->prepared('INSERT INTO `pd_grupos` (`nombre`, `comision`) VALUES (?, ?)', 'sd', [$nombre, $comision])) {
    echo $db->insert_id();
} else {
    echo 0;
}
