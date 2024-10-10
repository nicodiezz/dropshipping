<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
//require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';

$consulta = "SELECT * FROM `pd_grupos`";
$result = $db->query($consulta);
if (!$result){
    http_response_code(500); 
    die(json_encode(['error' => 'Database query failed']));
}

die(json_encode($result->fetch_all(MYSQLI_ASSOC)));
?>