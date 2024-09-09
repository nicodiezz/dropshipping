<?php

session_start();
if(!isset($_SESSION['isAdmin']) || !isset($_GET['reclamosPageNum']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require 'db.php';

$pedidos='`pd_reclamos`';
$vendedores='`pd_vendedores`';
$reclamos=$db->query("SELECT DATE_FORMAT($pedidos.`fecha`,'%e/%c/%Y') AS `fecha`,CASE WHEN $pedidos.`objeto`=0 THEN 'Aplicación' ELSE $vendedores.`nombre` END AS `objeto`,$pedidos.`reclamo` FROM $pedidos LEFT JOIN $vendedores ON $pedidos.`objeto`=$vendedores.`ID` ORDER BY $pedidos.`fecha` DESC LIMIT ".(5*((int)$_GET['reclamosPageNum'])).", 6")->fetch_all(MYSQLI_ASSOC);

$res=count($reclamos)==6?
	['reclamos'=>array_slice($reclamos,0,5),'hayMas'=>1]
	:['reclamos'=>$reclamos,'hayMas'=>0];

die(json_encode($res));

?>