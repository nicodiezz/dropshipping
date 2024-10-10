<?php
session_start(['read_and_close'=>true]);
if(!isset($_SESSION['ID']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
require '../db.php';

$baseQuery="SELECT * FROM `pd_pedidos_metadatos` WHERE `vendedorID`={$_SESSION['ID']} AND ";
$pedidosNuevos=$db->query($baseQuery.'`concretado`=2 ORDER BY `cuando` DESC,`ID` DESC');
$pedidosViejos=$db->query($baseQuery."`concretado`=1 ORDER BY `cuando` DESC,`ID` DESC LIMIT 16 OFFSET ".((isset($_GET['offset'])?(int)$_GET['offset']:0)*3));

$ped_art='`pd_pedidos_articulos`';
$arts='`pd_articulos`';

$rowCounter=0;
$response=[];

while($rowCounter<15 && $pedido=($pedidosNuevos->fetch_assoc()?:$pedidosViejos->fetch_assoc())){
	$items=[];
	//cambiosdeprecios es el nombre de un select interno
	$itemsRaw=$db->query(
		"SELECT
			$ped_art.*,
			$arts.`nombre`,
			cambiosdeprecio.`nuevoPrecio`
		FROM $ped_art
			INNER JOIN $arts
				ON $arts.`ID`=$ped_art.`articuloID`
			INNER JOIN (
				SELECT `articuloID`, `nuevoPrecio`
				FROM `pd_cambiosdeprecio`
				WHERE `ID` IN (
					SELECT MAX(ID)
					FROM `pd_cambiosdeprecio`
					WHERE `cuando`<='{$pedido['cuando']}'
					GROUP BY `articuloID`
				)
				ORDER BY `pd_cambiosdeprecio`.`articuloID` ASC
			) cambiosdeprecio
				ON cambiosdeprecio.`articuloID`=$ped_art.`articuloID`
		WHERE $ped_art.`pedidoID`=".$pedido['ID']
	);
	while($item=$itemsRaw->fetch_assoc())
		$items[]=[
			"cantidad"=>$item['cantidad'],
			"precioThen"=>$item['nuevoPrecio'],
			"articulo"=>$item['nombre'],
			"articuloID"=>$item['articuloID']
		];
	
	$esNuevo = $pedido['concretado']==2;
	
	if(!$esNuevo)
		$rowCounter++;
	
	$response[]=[
		"ID"=>$pedido['ID'],
		'time'=>date('d/m/Y G:i', strtotime($pedido['cuando'])),
		'nombre'=>$pedido['nombre'],
		'direccion'=>$pedido['direccion'],
		"items"=>$items,
		'esNuevo'=>$esNuevo
	];
}

die(json_encode([
	'pedidos'=>$response,
	'hayMas'=>$pedidosViejos->num_rows==16
]));
?>
