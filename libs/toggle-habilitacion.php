<?php

session_start(['read_and_close'=>true]);
if(!isset($_SESSION['isAdmin']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
require 'db.php';
$datos=json_decode(file_get_contents('php://input'),true);
$db->query("UPDATE `pd_vendedores` SET `habilitado`={$datos['hab']} WHERE `ID`={$datos['vendedorID']}");

?>