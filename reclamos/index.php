<?php

$personalizacion=parse_ini_file('../personalizacion.ini');
$nombre=$personalizacion['nombre'];

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	
	<title><?=$nombre?> - Reclamos</title>
	<meta property="og:title" content="<?=$nombre?> - Reclamos"/>
	<meta property="og:description" name="description" content="Bienvenido al centro de reclamos de <?=$nombre?>, aqui podrás dejar comentarios sobre tus desconformidades.">
	<!-- <meta property="og:image" content="img/logo.png"/> -->
	<meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?>"/>
	
	<link rel="stylesheet" href="/css/variables.css">
	<style>
		body, html {
				min-height: 100vh;}
			html{
				background: var(--color-principal);}
				body{
					background: white;
					margin: auto;
					font-family: sans-serif;}
					#header{
						display: grid;
						padding: 5px;}
						#header > * {
							height: min-content;margin: auto;}
							#header > img {
								margin: auto;
								height: 100%;width: 180px;
								object-fit: contain;
								grid-area: a;}
					form{
						display: flex;flex-direction: column;
						margin-top: 20px;}
						form > *{
							margin: auto;
							padding: 10px;}
							textarea{
								margin: 10px;
								resize: vertical;}
					
			@media screen and (min-aspect-ratio: 13/9) {
				body {
  				width: 80%;}
					#header{
						padding: 20px;
						grid-template: "a b" "a c";}
					form{
						width: 80%;margin: auto;}
			}
			@media screen and (max-aspect-ratio: 13/9) {
				body {
					width: 95%;}
					#header{
						grid-template: "a";gap: 20px;}
						#header > span{
							text-align: center;}
			}
	</style>
	<script src="/libs/c3tools.js"></script>
	<script>
		var vendedores=[
			{ID:0,nombre:'Aplicación'}
<?php
	require '../libs/db.php';
	
	$vendedores=$db->query('SELECT `ID`,`nombre` FROM `pd_vendedores` WHERE `habilitado`=1');
	while($vendedor=$vendedores->fetch_assoc())
		echo ','.json_encode($vendedor);
?>
		]
			;
		addEventListener('DOMContentLoaded',()=>{
			let datalist=gEt('vendedores');
			for(let vendedor of vendedores)
				datalist.append(createNode(
					'OPTION',{
						value:vendedor.ID
						,innerText:vendedor.nombre
					}
				));
			
			D.forms[0].onsubmit=function(){
				let complaint=this.complaint.value.trim()
					,objeto=this.objeto.value;
				if(complaint && objeto){
					let boton=gEt('boton');
					boton.disabled=true;
					startLoading('img/loading.gif');
					sendJSON('libs/registrar-reclamo.php',{objeto,complaint})
						.then(res=>res.text())
						.then(result=>{
							stopLoading();
							showMessage(...(
								+result?
									['Se ha registrado su reclamo.','lime']
									:['Ha ocurrido un error, intente nuevamente más tarde.','red']
							));
						})
						.catch(()=>{
							stopLoading();
							showMessage('Ha ocurrido un error, intente nuevamente más tarde.','red');
						})
						.finally(()=>boton.disabled=false);
				}
				return false;
			}
		})
	</script>
</head>
<body>
	<div id="header">
		<img src="/img/logo.png">
		<h1><?=$nombre?></h1>
		<span>Bienvenido al centro de reclamos de <?=$nombre?>, aqui podrás dejar comentarios sobre tus desconformidades.</span>
	</div>
	<form>
		
		<select id="vendedores" name="objeto">
			<option value> - Elija el objeto de su reclamo - </option>
		</select>
		
		<textarea name="complaint" rows="7" max-length=700 placeholder="Escriba en detalle aquí"></textarea>
		<input type="submit" value="Enviar" id=boton>
	</form>
</body>
</html>