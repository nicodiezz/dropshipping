<?php

session_start(['read_and_close'=>true]);

if(isset($_GET['vendedorID']))
	$vendedorID=(int)$_GET['vendedorID'];
elseif(isset($_SESSION['ID']))
	$vendedorID=(int)$_SESSION['ID'];
else $vendedorID=0;

if($vendedorID==0 || !file_exists($filename='../protected/'.md5('l '.$vendedorID)))
	$filename='not-found.png';
$size=getimagesize($filename);
$fp=fopen($filename,'rb');
if($size and $fp){
	header('Content-Type: '.$size['mime']);
	header('Content-length: '.filesize($filename));
	fpassthru($fp);
	die;
}

?>