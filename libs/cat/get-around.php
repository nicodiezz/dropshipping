<?php

session_start(['read_and_close'=>true]);

$vendedores='`pd_vendedores`';
$categorias='`pd_categorias`';

require '../db.php';
echo json_encode($db->query("SELECT DISTINCT $categorias.* FROM $categorias INNER JOIN $vendedores ON $vendedores.`categoriaID` =$categorias.`ID` WHERE $vendedores.`ID` IN({$_SESSION['vendedoresCerca']})")->fetch_all(MYSQLI_ASSOC));

?>