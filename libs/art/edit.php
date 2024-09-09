<?php

session_start(['read_and_close'=>true]);
require '../db.php';
$datosAdicionales=[];
if(isset($_FILES['foto'])){
	$id=(int)$_POST['editing'];
	$newPath='../../protected/'.md5($id);
	move_uploaded_file($_FILES['foto']['tmp_name'],$newPath);
	$size=getimagesize($newPath);
	$db->query("UPDATE `pd_articulos` SET `ext_de_img`='".explode('/',$size['mime'])[1]."' WHERE `ID`=".$id);
	$datosAdicionales['updateLogo']=true;
	$data=$_POST;
}else{
	$data=json_decode(file_get_contents('php://input'),true);
	$id=(int)$data['editing'];
}

if(!(isset($_SESSION['ID']) && $id && $db->result('SELECT `vendedorID` FROM `pd_articulos` WHERE `ID`='.$id)==$_SESSION['ID']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$datos=[];
$query='UPDATE `pd_articulos` SET ';
$addDato=function($dato,$dbcol,$nombre) use (&$datos,&$query,&$datosAdicionales){
	if(count($datos))
		$query.=',';
	$datos[]=$dato;
	$query.="`$dbcol`=?";
	$datosAdicionales['update'.$nombre]=true;
};

if(isset($data['nombre'])){
	$addDato(trim($data['nombre']),'nombre','Name');
}
if(isset($data['descripcion'])){
	$addDato(preg_replace('/\r?\n/','\n',trim($data['descripcion'])),'descripcion','Desc');
}
if(isset($data['codigo'])){
	$addDato((int)$data['codigo'],'codigo_de_barras','Code');
}
if(isset($data['precio'])){
	$precio=(float)$data['precio'];
	$addDato($precio,'precio','Precio');
	$db->query("INSERT INTO `pd_cambiosdeprecio` (`articuloID`,`nuevoPrecio`) VALUES ($id,$precio)");
}
if(isset($data['seccion'])){
	$addDato((int)$data['seccion'],'seccionID','Sec');
}
if(isset($data['dispo'])){
	$addDato((int)$data['dispo'],'disponible','Disp');
}
if(isset($data['destacado'])){
	$addDato((int)$data['destacado'],'destacado','Destacado');
}
$db->prepared($query.' WHERE `ID`='.$id,str_repeat('s',count($datos)),$datos);

die(json_encode($datosAdicionales));
?>