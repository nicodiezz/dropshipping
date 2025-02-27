<?php
session_start(['read_and_close'=>true]);
if(!(isset($_SESSION['ID']) && $_SESSION['habilitado']))
	header('Location: /login.php');
if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'])
	header('Location: /admin.php');
	
$personalizacion=parse_ini_file('personalizacion.ini');
$nombre=$personalizacion['nombre'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	<title><?=$nombre?> - Administración</title>
	<link rel="stylesheet" href="//use.fontawesome.com/releases/v5.15.1/css/all.css">
	<link rel="stylesheet" href="/css/variables.css">
	<link rel="stylesheet" href="/css/panel.css">
	<style>
		html{
			background:#<?=$_SESSION['color']?>;}
	</style>
	

	
	<script src="/libs/c3tools.js"></script>
	<script src="/panel/main.js"></script>

</head>
<body>
	<div id="bulk-message">
		<div>
			<div id="bulk-form">
				<span>El archivo debe ser un ZIP extraído de la página o un archivo de Microsoft Excel que cumpla el siguiente formato:</span>
				<div id="bulk-format-holder">
					<table id="bulk-format">
					<tbody>
						<tr><td></td> <td>A</td> <td>B</td> <td>C</td> <td>D</td> <td>E</td> <td>...</td></tr>
						<tr><td>1</td> <td></td> <td><b>Códigos</b></td> <td><b>Nombres</b></td> <td><b>Descripciones</b></td> <td><b>Precios</b></td> <td></td></tr>
						<tr><td>2</td> <td></td> <td>Código 1</td> <td>Nombre 1</td> <td>Descripción 1</td> <td>Precio 1</td> <td></td></tr>
						<tr><td>3</td> <td></td> <td>Código 2</td> <td>Nombre 2</td> <td>Descripcion 2</td> <td>Precio 2</td> <td></td></tr>
						<tr><td>...</td> <td></td> <td>...</td> <td>...</td> <td>...</td> <td>...</td> <td></td></tr>
					</tbody>
					</table>
				</div>
				<span>Defina el área donde se agregarán los archivos.</span>
				<div id="bulk-area">
					<label>
						<input type="radio" name="bulk-area-radio" value=0 checked class="bulk-tiny-input"><select class="bulk-area-input" id="bulk-area-select" disabled>
							<option value>Cargando...</option>
						</select>
					</label>
					<label>
						<input type="radio" name="bulk-area-radio" value=1 class="bulk-tiny-input"><input class="bulk-area-input" type="text" placeholder="Nueva área..." disabled>
					</label>
				</div>
				<label style="display:none">
					<input class="bulk-tiny-input" id="bulk-destacado" type="checkbox" onchange="if(this.checked)showMessage('Recuerde que no se recomienda destacar muchos artículos, lo recomendado es entre 4 y 10.')">
					<span>Destacar Todos</span>
				</label>
				<button id="bulk-select">Seleccionar Archivo</button>
			</div>
		</div>
	</div>
	<div id="header">
		<span id="header-anvorgesa" onclick="gEt('anvorgesa').classList.add('anvorgesa-abierta')">☰</span>
		<h1 id="header-nombre"><?=$_SESSION['nombre']?></h1>
	</div>
	
	<div id=anvorgesa>
		<div id="anvorgesa-header">
			<img src="img/logo.php" alt="Logo de <?=$_SESSION['nombre']?>" id=logo>
			<div id="anvorgesa-header-nombre"><?=$_SESSION['nombre']?></div>
			<div id="anvorgesa-header-usuario"><?=$_SESSION['usuario']?></div>
		</div>
		<div id="anvorgesa-ingredientes">
			<button class="action selected" value=pedidos>
				<i class="fa fa-receipt"></i>
				<span>Pedidos</span>
			</button>
			<button class="action" value=articulos>
				<i class="fa fa-boxes"></i>
				<span>Mis Artículos</span>
			</button>
			<button class="action" value=perfil>
				<i class="fa fa-user"></i>
				<span>Mi Perfil</span>
			</button>
			<button class="action" id=salir>
				<i class="fa fa-history"></i>
				<span>Cerrar Sesión</span>
			</button>
		</div>
	</div>

	<div id=content>
		<!-- <div id=realPanel> -->
			<form id=perfil class="hidden-scroll notSelectedPanel">
				
				<h3>Datos de la cuenta</h3>
				<div id="perfil-1">
					<div id="perfil-1-main">
						<p>Nombre:</p>
						<input name=nombre id=nombre value="<?=$_SESSION['nombre']?>" type="text">
						
						<button type=button id="perfil-contraseña" class=cute-button>Cambiar Contraseña</button>
						
						<p>URL:</p>
						<div id=perfil-asciiurl-holder>
							<button class="cute-button" type=button>
								<i class="far fa-copy"></i>
							</button>
							<input id=asciiurl value="<?=$_SESSION['nombreURL']?>" type="text" disabled>
						</div>
						
						<p>Mínimo de Compra:</p>
						<input name=minimoCompra value="<?=$_SESSION['minimoCompra']?>" type="number" step=1>
						
						<p>Nro de Teléfono:</p>
						<input name=numero value="<?=$_SESSION['numero']?>" type="number" step=1>
						
						
						
						<p>Descripción:</p>
						<textarea maxlength="100" name=descripcion rows=4><?=$_SESSION['descripcion']?></textarea>
					</div>
					<div class=imagePicker>
						<img src="img/logo.php">
						<input type=file class="cute-button">
						<button type=button onclick="resetImagePicker(this,'img/logo.php')" class="cute-button hidden">Reestablecer</button>
					</div>
				</div>
				
				<input type=submit value="Guardar Cambios" class="cute-button end-button" id="perfil-submit">
				<div style="font-size: 1px;color:var(--almost-white);">.</div>
			</form>
			<div id=articulos class=notSelectedPanel>
				
				<div id=art-inicio>
					<div id="busqueda">
						<input id="busqueda-input" type=text placeholder="Escriba aquí para filtrar artículos">
						
					</div>
					
					<div id="art-grid" class="hidden-scroll"></div>
				</div>
				<form id="art-añadir" class="hidden-scroll notSelectedPanel">
					<fieldset>
						<button type=button class="cute-button" id="art-añadir-volver">Volver</button>
						<h3 class="art-añadir-title">Datos del producto</h3>
						<p>Nombre</p>
						<input name=Nombre title="Nombre del Artículo" required class=cool-input>
						<p>Descripción</p>
						<textarea class=cool-input name=Descripcion title="Descripción detallada"></textarea>
						<p>Sección</p>
						<select class=cool-input name=Seccion id=new-art-sec-input disabled>
							<option value>Cargando...</option>
						</select>
						<p>Precio</p>
						<div id="art-precio-holder">
							<span>$ </span><input class="cool-input" name=Precio type="number" step="0.01" required>
						</div>
						<p>Código de barra</p>
						<input class=cool-input name="codigo" title="Código de Barra (13 números)" maxlength="13">
						<h3>Foto del producto</h3>
						<div class=imagePicker>
							<img src="img/not-found.png">
							<input type=file name=foto class="cute-button">
							<button type=button onclick="resetImagePicker(this,'img/articulo.php?ID='+editing)" class="cute-button hidden">Reestablecer</button>
						</div>
						<input id="art-subir-boton" type=submit value="Subir Artículo" class="cute-button end-button">
					</fieldset>
				</form>
			</div>
			<div id="pedidos">
				<div id="pedidos-botones" data-showing=0>
					<button id="pedidos-botones-pendientes" value=0 class="pedidos-selected">Pendientes</button>
					<button id="pedidos-botones-historial" value=1>Historial</button>
				</div>
				<div id="pedidos-pendientes" class="hidden-scroll">
				<div></div>
				</div>
				<div id="pedidos-historial" class="hidden-scroll">
					<div id=ped-botones>
						<button id=ped-mas>Cargar Más</button>
						<button id=ped-exportar>Exportar</button>
					</div>
					<div id=ped-historial></div>
				</div>
			</div>
	</div>
</body>
</html>