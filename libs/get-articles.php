<?php

session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!isset($_SESSION['ID']))
	require DR.'/libs/header-location.php';
require '../libs/db.php';
$categorias_response=$db->query("SELECT * FROM pd_vendedores_categorias WHERE vendedorID=".$_SESSION['ID']);

$articulos=[];
if($categorias_response && $categorias_response->num_rows){
	while($categoria = $categorias_response->fetch_assoc()) {
		$articulo_response= $db->query("SELECT * FROM pd_articulos WHERE categoriaID=".$categoria["categoriaID"]);
		if($articulo_response && $articulo_response->num_rows){
			while ($articulo = $articulo_response->fetch_assoc()){
				$articulos[] = $articulo_response->fetch_assoc();
			}		
		}
	}
}

if(count($articulos)){
	die(json_encode($articulos));
}else die('[]');
