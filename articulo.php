<?php

define('DR',$_SERVER['DOCUMENT_ROOT']);
function aCasa(){
	require DR.'/libs/header-location.php';
}
require 'libs/db.php';

$personalizacion=parse_ini_file('personalizacion.ini');
$nombre=$personalizacion['nombre'];

if(isset($_GET['ID']))
	$ID=(int)$_GET['ID'];
else aCasa();

$thisArticulo=$db->query('SELECT * FROM `pd_articulos` WHERE `disponible`=1 AND `ID`='.$ID);
if(!$thisArticulo->num_rows)
	aCasa();
$thisArticulo=$thisArticulo->fetch_assoc();
$thisVendedor=$db->query("SELECT * FROM `pd_vendedores` WHERE `ID`=".$thisArticulo['vendedorID'])->fetch_assoc();
	
$personalizacion=parse_ini_file('personalizacion.ini');
$nombre=$personalizacion['nombre'];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	
	<title><?=$nombre?> - <?=$thisArticulo['nombre']?> - Articulo de <?=$thisVendedor['nombre']?></title>
	<meta property="og:title" content="<?=$nombre?> - <?=$thisArticulo['nombre']?> - Articulo de <?=$thisVendedor['nombre']?>"/>
	<meta property="og:description" name="description" content="<?=$thisArticulo['descripcion']?>">
	<meta property="og:image" content="img/articulo.php?ID=<?=$thisArticulo['ID']?>"/>
	<meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?>/articulo.php?ID=<?=$thisArticulo['ID']?>"/>
	
	<style>
			html{
				background:#<?=$thisVendedor['color']?>;
				margin:0;
				font-family:sans-serif;}
				body{
					background:white;
					min-height:100vh;display: grid;/*display:grid; previene que el margen de #content salga de body*/ 
					margin:auto;}
					#header{
						display: flex;
						width: 90%;
						margin:auto;
						border-bottom: solid lightgray;}
						#header > a{
							margin: auto;
							cursor: pointer;}
							#header img{
								margin: auto;
								height: 100%;width: 180px;object-fit: contain;}
						#header > div{
							margin:auto;}
							#header h1{
								text-align: center;}
					#content{
						text-align: center;
						width: 80%;margin: 20px auto;
						display: grid;grid-template: "a b" min-content "a c" 1fr "d d" / 1fr 1fr;
						padding: 20px;border-radius: 20px; border:solid;}
<?php if((int)$thisArticulo['destacado']){ ?>
						#content::before {
							position: absolute;
							content: '⭐ Artículo Destacado';font-size: x-large;
							background: rgba(200,200,200,.5);padding: 5px;border-radius: 15px;}
<?php } ?>
						#image{
							grid-area: a;
							max-height: 50vh;}
							#image img{
								object-fit: contain;width: 100%;height: 100%;}
						#precio{
							grid-area: d;
							color:green;font-weight: bold;font-family: sans-serif;}
							#precio:before{
								content:'$ ';}
		@media screen and (min-aspect-ratio: 13/9) {
			body{
				width:80%;}
				#header{
					padding:20px;}
					#header > div{
						width: 50%;}
				#desc{
					padding: 20px;}
		}
		@media screen and (max-aspect-ratio: 13/9) {
			body{
				width:95%;
				padding-bottom: 1px;}
				#header{
					flex-direction: column;
					padding:10px;}
				#content{
					display: flex;flex-direction: column;}
		}
	</style>
	<script src="/libs/c3tools.js"></script>
	<script>
		
	</script>
</head>
<body>
	<div id=header>
		<a href="/<?=$thisVendedor['nombreURL']?>">
			<img src="img/logo.php?vendedorID=<?=$thisVendedor['ID']?>">
		</a>
		<div>
			<h1><?=$thisVendedor['nombre']?></h1>
			<div>
				<span><?=$thisVendedor['descripcion']?></span>
			</div>
		</div>
	</div>
	<div id=content>
		<div id=image><img src="img/articulo.php?ID=<?=$thisArticulo['ID']?>"></div>
		<h1><?=$thisArticulo['nombre']?></h1>
		<div id=desc><?=$thisArticulo['descripcion']?></div>
		<h1 id=precio><?=$thisArticulo['precio']?></h1>
	</div>
</body>
</html>