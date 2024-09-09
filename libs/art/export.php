<?php

session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);

if(!isset($_SESSION['ID']))
	require DR.'/libs/header-location.php';

$zipArchive = new ZipArchive();
$thisRand=rand();
$baseName ="articulos-$thisRand.zip";
$zipPath ="temp/$baseName";
if($zipArchive->open($zipPath,  ZipArchive::CREATE)===false){
	$error=$zipArchive->getStatusString();
	$zipArchive->close();
	switch( (int) $error ){
		case ZipArchive::ER_OK           : $error='N No error';
		case ZipArchive::ER_MULTIDISK    : $error='N Multi-disk zip archives not supported';
		case ZipArchive::ER_RENAME       : $error='S Renaming temporary file failed';
		case ZipArchive::ER_CLOSE        : $error='S Closing zip archive failed';
		case ZipArchive::ER_SEEK         : $error='S Seek error';
		case ZipArchive::ER_READ         : $error='S Read error';
		case ZipArchive::ER_WRITE        : $error='S Write error';
		case ZipArchive::ER_CRC          : $error='N CRC error';
		case ZipArchive::ER_ZIPCLOSED    : $error='N Containing zip archive was closed';
		case ZipArchive::ER_NOENT        : $error='N No such file';
		case ZipArchive::ER_EXISTS       : $error='N File already exists';
		case ZipArchive::ER_OPEN         : $error='S Can\'t open file';
		case ZipArchive::ER_TMPOPEN      : $error='S Failure to create temporary file';
		case ZipArchive::ER_ZLIB         : $error='Z Zlib error';
		case ZipArchive::ER_MEMORY       : $error='N Malloc failure';
		case ZipArchive::ER_CHANGED      : $error='N Entry has been changed';
		case ZipArchive::ER_COMPNOTSUPP  : $error='N Compression method not supported';
		case ZipArchive::ER_EOF          : $error='N Premature EOF';
		case ZipArchive::ER_INVAL        : $error='N Invalid argument';
		case ZipArchive::ER_NOZIP        : $error='N Not a zip archive';
		case ZipArchive::ER_INTERNAL     : $error='N Internal error';
		case ZipArchive::ER_INCONS       : $error='N Zip archive inconsistent';
		case ZipArchive::ER_REMOVE       : $error='S Can\'t remove file';
		case ZipArchive::ER_DELETED      : $error='N Entry has been deleted';
		
		default: $error=sprintf('Unknown status %s', $error );
	}
	die($error);
}
require '../db.php';
$result=$db->query(
	"SELECT * FROM `pd_articulos` WHERE
		`ID` IN(".join(',',array_map(fn($ID)=>(int)$ID,explode(',',$_GET['articles']))).")
		AND `vendedorID`={$_SESSION['ID']}"
);

$i=0;
$rows=[["","Códigos","Nombres","Descripciones","Precios"]];
while($img=$result->fetch_assoc()){
	$i++;
	if($img['ext_de_img']){
		$imageName="articulo-$i.".$img['ext_de_img'];
		$imagePath='../../protected/'.md5($img['ID']);
		// if(file_exists($imagePath)) //if it doesnt exist, ext_de_img should be null. it doesnt throw any error either anyways, so...
		$zipArchive->addFile($imagePath , $imageName);
	}else $imageName=$i;
	$rows[]=[$imageName,$img['codigo_de_barras'],$img['nombre'],$img['descripcion'],$img['precio']];
}

require DR.'/libs/PHP/utils/Excel/SimpleXLSXGen.php';
$zipArchive->addFromString('articulos.xlsx',SimpleXLSXGen::fromArray($rows));//->__toString());

$zipArchive->close();

header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=$baseName");
header("Content-Length: ".filesize($zipPath));

readfile($zipPath);

unlink($zipPath);

?>