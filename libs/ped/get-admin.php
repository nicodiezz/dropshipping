<?php
session_start(['read_and_close'=>true]);
if(!isset($_SESSION['isAdmin']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
require '../db.php';
$datos=json_decode(file_get_contents('php://input'),true);
$str='';
if($datos['from'])
	$str.=" AND `cuando` >= '{$datos['from']}'";
if($datos['until'])
	$str.=" AND `cuando` <= '{$datos['until']}'";
$pedidos=$db->query("SELECT *,(
	SELECT `precio`
	FROM `pd_cambiosdelivery`
	WHERE `cuando`<=`pd_pedidos_metadatos`.`cuando`
	ORDER BY `cuando` DESC LIMIT 1
) AS `precioDelivery` FROM `pd_pedidos_metadatos` WHERE `vendedorID`=".(int)$datos['ID'].' AND `concretado`=1'.$str.' ORDER BY `cuando` DESC, `ID` DESC');
$response=[];
while($pedido=$pedidos->fetch_assoc()){
	$items=[];
	$itemsRaw=$db->query("SELECT `pd_pedidos_articulos`.*, `pd_articulos`.`nombre`,cambiosdeprecio.`nuevoPrecio` FROM `pd_pedidos_articulos` INNER JOIN `pd_articulos` ON `pd_articulos`.`ID`=`pd_pedidos_articulos`.`articuloID` INNER JOIN (SELECT `articuloID`,MAX(`cuando`),`nuevoPrecio` FROM `pd_cambiosdeprecio` WHERE `cuando`<'{$pedido['cuando']}' GROUP BY `articuloID`) cambiosdeprecio ON cambiosdeprecio.`articuloID`=`pd_pedidos_articulos`.`articuloID` WHERE `pd_pedidos_articulos`.`pedidoID`=".$pedido['ID']);
	while($item=$itemsRaw->fetch_assoc()){
		$items[]=[
			"cantidad"=>$item['cantidad']
			,"precioThen"=>$item['nuevoPrecio']
			,"articulo"=>$item['nombre']
			,"articuloID"=>$item['articuloID']
		];
	}
	if($pedido['delivery'])
		$items[]=[
			"cantidad"=>1
			,"precioThen"=>$pedido['precioDelivery']
			,"articulo"=>'Delivery'
			,"articuloID"=>0
		];
	$response[]=[
		"ID"=>$pedido['ID']
		,"titulo"=>date('d/m/Y',strtotime($pedido['cuando'])).', '.$pedido['nombre']
		,"items"=>$items
	];
}
die(json_encode($response));
?>