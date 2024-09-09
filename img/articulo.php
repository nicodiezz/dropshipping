<?php
if(isset($_GET['ID'])){
	$filename='../protected/'.md5($_GET['ID']);
	if(!file_exists($filename))
		$filename='not-found.png';
}else $filename='not-found.png';
$size=getimagesize($filename);
$fp=fopen($filename,'rb');
if($size and $fp){
	header('Content-Type: '.$size['mime']);
	header('Content-length: '.filesize($filename));
	fpassthru($fp);
	die;
}
?>