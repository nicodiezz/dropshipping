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
	$str.=" AND `cuando` <= '{$datos['until']} 23:59:59'";
$pedidos=$db->query("SELECT * FROM `pd_pedidos_metadatos` WHERE `vendedorID`=".(int)$datos['ID'].' AND `concretado`=1'.$str.' ORDER BY `cuando` DESC, `ID` DESC');
$response=[];

while($pedido=$pedidos->fetch_assoc()){
	$items=[];
	
	$itemsRaw = $db->query("		
		SELECT pa.articuloID, a.nombre, last_precio.precio, pa.cantidad
		FROM pd_pedidos_articulos pa
		JOIN 
		(
		    SELECT cp.articuloID, cp.nuevoPrecio as precio
		    FROM pd_cambiosdeprecio cp
		    JOIN 
		    (
		        SELECT articuloID, MAX(cuando) as max_cuando
		        FROM pd_cambiosdeprecio
		        WHERE 1=1".$str ."
		        GROUP BY articuloID
		    ) max_dates 
		    ON cp.articuloID = max_dates.articuloID AND cp.cuando = max_dates.max_cuando
		) as last_precio
		ON pa.articuloID = last_precio.articuloID
		JOIN pd_articulos a 
		ON pa.articuloID = a.ID 
		WHERE pa.pedidoID = {$pedido['ID']};"
	);
	while($item=$itemsRaw->fetch_assoc()){
		$items[]=[
			"cantidad"=>$item['cantidad']
			,"precioThen"=>$item['precio']
			,"articulo"=>$item['nombre']
			,"articuloID"=>$item['articuloID']
		];
	}
	$response[]=[
		"ID"=>$pedido['ID']
		,"titulo"=>date('d/m/Y',strtotime($pedido['cuando'])).', '.$pedido['nombre']
		,"items"=>$items
	];
}
die(json_encode($response));
?>
