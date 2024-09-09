<?php
define('DR',$_SERVER['DOCUMENT_ROOT']);
session_start(['read_and_close'=>true]);

if(!(isset($_FILES['newImage']) && isset($_SESSION['ID']) && isset($_POST['ID'])))
	require DR.'/libs/header-location.php';
	
	
echo move_uploaded_file($_FILES['newImage']['tmp_name'],'../protected/'.md5($_POST['ID']))?
	1
	:0;
?>