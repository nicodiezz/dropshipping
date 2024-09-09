<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
//require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';

$consulta="SELECT DISTINCT `pd_categorias`.* FROM `pd_categorias`";
if(!isset($_SESSION['isAdmin']))
	$consulta.=" INNER JOIN `pd_vendedores` ON `pd_categorias`.`ID` = `pd_vendedores`.`categoriaID` WHERE `pd_vendedores`.`habilitado`=1";
die(json_encode($db->query($consulta)->fetch_all(MYSQLI_ASSOC)));
	
?>