<?php

ini_set('display_errors', 1);

session_start();
if(!isset($_SESSION['ID']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$datosAdicionales=[];

if(isset($_FILES['newpfp'])){
	$data=$_POST;
	move_uploaded_file($_FILES['newpfp']['tmp_name'],'../protected/'.md5('l '.$_SESSION['ID']));
	$datosAdicionales['reloadLogo']=true;
}else $data=json_decode(file_get_contents('php://input'),true);

$datos=[];
$sets=[];

if(isset($data['nombre'])){
	require 'utf8-to-ascii.php';
	
	$sets[]='`nombre`=?';
	$datos[]=$data['nombre'];
	$_SESSION['nombre']=$data['nombre'];
	
	$sets[]='`nombreURL`=?';
	$URL=toAsciiURL($data['nombre']);
	$datos[]=$URL;
	$datosAdicionales['newURL']=$URL;
	$_SESSION['nombreURL']=$URL;
}
if(isset($data['color'])){
	$sets[]='`color`=?';
	$colorNormalizado=strtoupper(substr($data['color'],1));
	$datos[]=$colorNormalizado;
	$_SESSION['color']=$colorNormalizado;
	$datosAdicionales['cambiarColor']=true;
}

if(isset($data['coords'])){
	$coords=array_map(function($coord){return (float)$coord;},explode(',',$data['coords']));
	$sets[]='`lon`='.$coords[0].',`lat`='.$coords[1];
	$_SESSION['lon']=$coords[0];
	$_SESSION['lat']=$coords[1];
	
}

//simple
foreach([
	'descripcion'
	,'pais'
	,'numero'
	,'provincia'
	,'ciudad'
	,'direccion'
	,'minimoCompra'
] as $attr)
	if(isset($data[$attr])){
		$sets[]="`$attr`=?";
		$datos[]=$data[$attr];
		$_SESSION[$attr]=$data[$attr];
	}

session_regenerate_id();

require 'db.php';
if(count($sets)){
	$cantDatos=count($datos);
	if($cantDatos)
	$db->prepared('UPDATE `pd_vendedores` SET '.join(',',$sets).' WHERE `ID`='.$_SESSION['ID'],str_repeat('s',count($datos)),$datos);
	else $db->query('UPDATE `pd_vendedores` SET '.join(',',$sets).' WHERE `ID`='.$_SESSION['ID']);
}

echo json_encode($datosAdicionales);
?>