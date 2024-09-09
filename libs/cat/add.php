<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(!(isset($_SESSION['isAdmin']) && isset($data['nuevoNombre'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';
if($db->prepared('INSERT INTO `pd_categorias` (`nombre`) VALUES (?)','s',trim($data['nuevoNombre'])))
	echo $db->insert_id();
else echo 0;
?>