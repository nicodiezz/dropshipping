<?php
session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);

if(!(
	isset($_SESSION['ID'])
	&& isset($_GET['IDs'])
	&& (
		!$_SESSION['isAdmin']
		|| isset($_GET['vendedorID'])
	)
))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';

$inClause=($IDs=trim($_GET['IDs']))?
	'AND `ID` IN('
	.join(
		','
		,array_map(
			fn($n)=>(int)$n
			,explode(
				','
				,$IDs
			)
		)
	)
	.')'
	:'';
$vendedorID=$_SESSION['isAdmin']?
	(int)$_GET['vendedorID']
	:$_SESSION['ID'];
$pedidosRaw=$db->query("SELECT * FROM `pd_pedidos_metadatos` WHERE `vendedorID`=$vendedorID AND `concretado`=1 $inClause ORDER BY `cuando` DESC");
$resultado=[['Fecha','Nombre','Pedido','Precio Total']];
while($pedido=$pedidosRaw->fetch_assoc()){
	$itemsRaw=$db->query(
		"SELECT `pd_pedidos_articulos`.`cantidad`, `pd_articulos`.`nombre`,cambiosdeprecio.`nuevoPrecio`
		FROM `pd_pedidos_articulos`
		INNER JOIN `pd_articulos` ON `pd_articulos`.`ID`=`pd_pedidos_articulos`.`articuloID`
		INNER JOIN (
			SELECT `articuloID`,MAX(`cuando`),`nuevoPrecio`
			FROM `pd_cambiosdeprecio`
			WHERE `cuando`<'{$pedido['cuando']}' GROUP BY `articuloID`
		) cambiosdeprecio ON cambiosdeprecio.`articuloID`=`pd_pedidos_articulos`.`articuloID`
		WHERE `pd_pedidos_articulos`.`pedidoID`=".$pedido['ID']
	);
	$total=0;
	$detalle=[];
	
	while($item=$itemsRaw->fetch_assoc()){
		$total+=(float)$item['nuevoPrecio'];
		$detalle[]=$item['cantidad'].' × '.$item['nombre'].', a $ '.$item['nuevoPrecio'].' c/u.';
	}
	
	$resultado[]=[
		date('d/m/Y',strtotime($pedido['cuando']))
		,$pedido['nombre']
		,join("\n",$detalle)
		,$total
	];
}

require DR.'/libs/PHP/utils/Excel/SimpleXLSXGen.php';
die(SimpleXLSXGen::fromArray($resultado));
?>