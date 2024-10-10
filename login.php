<?php
	session_start(['read_and_close'=>true]);
	$personalizacion=parse_ini_file('personalizacion.ini');
	$nombre=$personalizacion['nombre'];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	
	<title><?=$nombre?> - Login</title>
	<meta property="og:title" content="<?=$nombre?> - Login"/>
	<meta property="og:description" name="description" content="Acceso al panel de control para vendedores y administradores.">
	<!--TODO add logo <meta property="og:image" content="img/logo.png"/> -->
	<meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?>/login.php"/>

	<link rel="stylesheet" href="/css/variables.css">
	<link rel="stylesheet" href="/login/main.css">
	
	
	

	<script src="/libs/c3tools.js"></script>
	<script >
		
		
	
		function setFormDisabled(boolean){
			gEt('loading').style.display=boolean?'flex':'none';
			for(let input of SqS('input, button',ALL))
				input.disabled=boolean;
		}
		
		function haOcurridoUnErrorInesperado(error){
			console.log(error);
			setFormDisabled(false);
			alert('Ha ocurrido un error inesperado, reintente nuevamente más tarde.');
		}
		
		function crearLogin(){
			addNode(gEt('campos')
				,['INPUT',{
					id:'user'
					,name:'user'
					,placeholder:'Usuario'
					,required:true
				}]
				,['INPUT',{
					id:'pass'
					,name:'pass'
					,type:'password'
					,placeholder:'Contraseña'
					,required:true
				}]
			);
			addNode(gEt('botones')
				,['INPUT',{
					type:'submit'
					,value:'Entrar'
				}]
				,['BUTTON',{
					type:'button'
					,innerText:'Registrarse'
					,onclick:()=>{
						gEt('register').style.display='flex';
					}
				}]
			);
		}
		
		addEventListener('DOMContentLoaded',()=>{
			
			[...SqS('.over',ALL)].map(el=>el.style.display='none');
			
			gEt('loading').classList.remove('loading-initial');
			
			////forms
			
			D.forms.contenedor.onsubmit=function(){
				setFormDisabled(true);
				sendJSON('libs/login-backend.php',{user:this.user.value,pass:this.pass.value})
					.then(r=>r.text())
					.then(r=>{
						if(!r)
							setFormDisabled(false);
						else if(4==r)//access granted
							location.replace('panel.php');
						else if(5==r)//access granted for admins
							location.replace('admin.php');
						else{
							setFormDisabled(false);
							switch(+r){
							case 1://bad username
								showMessage('Nombre de usuario erróneo.','red');
								break;
							case 2://bad password
								showMessage('Contraseña errónea.','red');
								break;
							case 3://not allowed
								showMessage('Su cuenta se encuentra deshabilitada.','red');
								break;
							default:
								console.log(r);
								break;
							}
						}
					})
					.catch(haOcurridoUnErrorInesperado);
				return false;
			}
			
			D.forms.registerForm.onsubmit=function(){
				setFormDisabled(true);
				let data={
					username:this.username.value
					,password:this.password.value
					,negocioname:this.negocioname.value
					,numero:'54'+this.ca.value+this.numero.value
					,categoria:(this.categoria.value==-1?
						this.categoriaNueva.value
						:this.categoria.value
					)
				}
				for(let key in data){
					data[key]=data[key].trim();
					if(!data[key]){
						alert('No debe dejar campos vacíos.');
						setFormDisabled(false);
						return false;
					}
				}
				if(data.categoria)
					sendJSON('libs/register.php',data)
						.then(r=>r.text())
						.then(response=>{
							let responseAsNumber=+response;
							if(responseAsNumber)
								location.replace('panel.php');
							else{
								setFormDisabled(false);
								console.log(response);
							}
						})
						.catch(haOcurridoUnErrorInesperado);
				return false;
			}
			gEt('register').onclick=function(e){
				if(this==e.target)
					this.style.display='none';
			};
			
			D.forms.registerForm.onkeydown=e=>{
				if(e.target.nodeName!='TEXTAREA')
					return event.key != 'Enter';
			}
			
			//UI building
<?php

if(isset($_SESSION['ID']))
	echo "
			addNode(gEt('campos')
				,['SPAN',{
					innerText:'Ya existe una sesión.'
					,style:{
						fontSize: 'x-large'
					}
				}]
				,['SPAN',{innerText:'¿Cómo desea proceder?'}]
			);
			addNode(gEt('botones')
				,['BUTTON',{
					type:'button'
					,innerText:'Entrar'
					,onclick:()=>location.replace('".($_SESSION['isAdmin']?'admin':'panel').".php')
				}]
				,['BUTTON',{
					type:'button'
					,innerText:'Cerrar Sesión'
					,onclick:function(){
						let botonesDiv=this.parentNode;
						botonesDiv.previousElementSibling.innerHTML='';
						botonesDiv.innerHTML='';
						fetch('libs/logout.php');
						crearLogin();
					}
				}]
			);";
else echo '			crearLogin();';

?>
		});
		
	</script>
</head>
<body>
	<div class="loading-initial over" id=loading><img src="img/loading.gif"></div>
	<h1 id="header">Panel de Administracion</h1>
	<div class=over id=register>
		<form class=card id=registerForm>
			<!-- <fieldset> TODO see this: make this scroll instead of the whole thing--> 
				<span>Complete el siguiente formulario.</span>
				<input type="text" name=username placeholder="Nombre de Usuario" title="Solo se usa para el acceso." required>
				<input type="text" name=password placeholder="Contraseña" required>
				<input type="text" name=negocioname placeholder="Nombre de negocio" title="Se puede cambiar más adelante." required>
				<span>Contacto:</span>
				<div>
					54
					<input type="number" name=ca placeholder="C. Área" style="width:70px" required>
					<input type="number" name=numero placeholder="Número de teléfono" required>
				</div>
				<label >Categoría: <select name="categoria" required>
					<option value>Elija una opción...</option>
<?php

require 'libs/db.php';

$options=$db->query("SELECT * FROM `pd_categorias`");
while($option=$options->fetch_assoc()){
echo "<option value={$option['ID']}>{$option['nombre']}</option>";
}
?>
				</select></label>
			<input type="submit" value="Registrarme">
		</form>
	</div>
	<form id=contenedor class=card method=post>
		<img src="img/logo.png">
		<div id=campos></div>
		<div id=botones></div>
	</form>
</body>
</html>