<?php
$data=json_decode(file_get_contents('php://input'),true);
$data['vendedorID']=(int)$data['vendedorID'];
$data['nombre']=trim($data['nombre']);
$data['direccion']=trim($data['direccion']);
$data['delivery']=(int)$data['delivery'];

if(
	!(
		$data['vendedorID']
		&& $data['nombre']
		&& $data['direccion']
		&& count($data['items'])
	)
)
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require 'db.php';
$db->prepared("INSERT INTO `pd_pedidos_metadatos` (`vendedorID`,`nombre`,`direccion`,`delivery`) VALUES ({$data['vendedorID']},?,?,{$data['delivery']})",'ss',[$data['nombre'],$data['direccion']]);
$pedidoID=$db->insert_id();
$query='INSERT INTO `pd_pedidos_articulos` (`pedidoID`,`articuloID`,`cantidad`) VALUES ';
foreach($data['items'] as $key=>$item){
	if((int)$key)
		$query.=',';
	$query.="($pedidoID,".(int)$item[0].",".(int)$item[1].")";
}
$db->query($query);
?>