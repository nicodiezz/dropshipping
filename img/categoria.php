<?php
if(isset($_GET['ID'])){
	$filename='../protected/'.md5('c '.$_GET['ID']);
	if(!file_exists($filename))
		$filename='categoria.png';
}else $filename='categoria.png';
$size=getimagesize($filename);
$fp=fopen($filename,'rb');
if($size and $fp){
	header('Content-Type: '.$size['mime']);
	header('Content-length: '.filesize($filename));
	fpassthru($fp);
	die;
}
?>