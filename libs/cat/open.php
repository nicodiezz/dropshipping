<?php
require '../db.php';
session_start(['read_and_close'=>true]);

$search=(int)$_GET['catID'];
if($search)
	// echo json_encode($db->query("SELECT * FROM `pd_vendedores` WHERE `categoriaID`=$search AND `habilitado`=1 ORDER BY RAND() AND `ID` IN({$_SESSION['vendedoresCerca']})")->fetch_all(MYSQLI_ASSOC));
	echo json_encode($db->query("SELECT * FROM `pd_vendedores` WHERE `categoriaID`=$search AND `habilitado`=1 ORDER BY RAND()")->fetch_all(MYSQLI_ASSOC));
else header('/',true,301);
?>