<?php

session_start(['read_and_close'=>true]);
require '../db.php';

if(!(isset($_SESSION['isAdmin'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$precio=(float)$_POST['Precio'];

$size=getimagesize($_FILES['foto']['tmp_name']);
$db->prepared(
	"INSERT INTO `pd_articulos` (
		`nombre`
		,`descripcion`
		,`precio`
		,`grupoID`
		,`seccionID`
		,`codigo_de_barras`
		,`ext_de_img`
	) VALUES (
		?
		,?
		,?
		,?
		,?
		,?
		,?
	)"
	,'ssiiiis'
	,[
		trim($_POST['Nombre'])
		,preg_replace("/\r?\n/",'\n',trim($_POST['Descripcion']))
		,$precio
		,(int)$_POST['grupoID']
		,(int)$_POST['Seccion']
		,(int)$_POST['codigo']
		,explode('/',$size['mime'])[1]
	]
);
$id=$db->insert_id();

$db->query("INSERT INTO `pd_cambiosdeprecio` (`articuloID`,`nuevoPrecio`) VALUES ($id,$precio)");

move_uploaded_file($_FILES['foto']['tmp_name'],'../../protected/'.md5($id));
	
die(
	json_encode(
		$db->query("SELECT * FROM `pd_articulos` WHERE `ID`=".$id)->fetch_assoc()
	)
);

?>