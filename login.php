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
	
	<link rel="stylesheet" href="/libs/OpenLayers/ol.css">
	<script src="/libs/OpenLayers/ol.js"></script>
	
	<script src="/libs/c3tools.js"></script>
	<script >
		var requestNro=0;
		
		// map "var"s
		var map
			,fromLonLat=ol.proj.fromLonLat
			,toLonLat=ol.proj.toLonLat
			,PointerInteraction=ol.interaction.Pointer
			,Drag =(function (PointerInteraction) {
				function Drag() {
					PointerInteraction.call(this, {
						handleDownEvent: function(evt) {
							let map = evt.map,
								feature = map.forEachFeatureAtPixel(evt.pixel,feature=>feature);
							if (feature) {
								this.coordinate_ = evt.coordinate;
								this.feature_ = feature;
							}
							return !!feature;
						},
						handleDragEvent: function(evt) {
							let deltaX = evt.coordinate[0] - this.coordinate_[0]
								,deltaY = evt.coordinate[1] - this.coordinate_[1]
								,geometry = this.feature_.getGeometry();
								
							geometry.translate(deltaX, deltaY);
							
							this.coordinate_[0] = evt.coordinate[0];
							this.coordinate_[1] = evt.coordinate[1];
						},
						handleMoveEvent: function (evt) {
							if (this.cursor_) {
								let map = evt.map
									,feature = map.forEachFeatureAtPixel(evt.pixel,feature=> feature)
									,element = evt.map.getTargetElement();
								if (feature) {
									if (element.style.cursor != this.cursor_) {
										this.previousCursor_ = element.style.cursor;
										element.style.cursor = this.cursor_;
									}
								} else if (this.previousCursor_ !== undefined) {
									element.style.cursor = this.previousCursor_;
									this.previousCursor_ = undefined;
								}
							}
						},
						handleUpEvent: function () {
							let coords=toLonLat(markerPoint.getCoordinates());
							fillPlaceFields([coords[1],coords[0]]);
							this.coordinate_ = null;
							this.feature_ = null;
							return false;
						}
					});
					this.coordinate_ = null;
					this.cursor_ = 'pointer';
					this.feature_ = null;
					this.previousCursor_ = undefined;
				}
				if ( PointerInteraction )
					Drag.__proto__ = PointerInteraction;
				Drag.prototype = Object.create( PointerInteraction && PointerInteraction.prototype );
				Drag.prototype.constructor = Drag;
				return Drag;
			}(PointerInteraction))
			
			,markerPoint=new ol.geom.Point([0, 0])
			,fill=new ol.style.Fill({
				color: 'rgba(255,255,255,0.3)'
			})
			,stroke=new ol.style.Stroke({
				color: '#56E48E',
				width: 2
			});
	
		//funs
		
		function pointMapTo(coords,zoom){
			coords=fromLonLat(coords);
			markerPoint.setCoordinates(coords);
			map.getView().setCenter(coords);
			map.getView().setZoom(zoom);
		}
	
		function setFormDisabled(boolean){
			gEt('loading').style.display=boolean?'flex':'none';
			for(let input of SqS('input, button',ALL))
				input.disabled=boolean;
		}
		
		function fillPlaceFields(coords){
			let thisRequest=++requestNro;
			fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat='+coords[0]+'&lon='+coords[1]+'&zoom=10')
				.then(res=>{
					if(thisRequest==requestNro)
						return res.json()
				})
				.then(city=>{
					if(thisRequest==requestNro){
						if(city.error)
							city.address={
								country:''
								,state:''
								,city:''
							}
						let current;
						for(let part of [
							['pais','country']
							,['provincia','state']
						])
							gEt(part[0]).value=city.address[part[1]]||'';
						gEt('ciudad').value=city.address['city']||city.address['town']||'';
					}
				});
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
						navigator.geolocation.getCurrentPosition(result=>{
							pointMapTo([+result.coords.longitude,+result.coords.latitude],15);
							fillPlaceFields([result.coords.latitude,result.coords.longitude]);
							gEt('map').style.display='block';
						},null,{enableHighAccuracy: true});
						gEt('register').style.display='flex';
					}
				}]
			);
		}
		
		addEventListener('DOMContentLoaded',()=>{
			
			// map stuff
			
			map = new ol.Map({
				interactions: ol.interaction.defaults().extend([new Drag()]),
				layers: [
					new ol.layer.Tile({
						source: new ol.source.OSM()
					})
					,new ol.layer.Vector({
						source: new ol.source.Vector({
							features: [new ol.Feature({
								type: 'geoMarker',
								geometry: markerPoint
							})]
						})
						,style: new ol.style.Style({
							image: new ol.style.Circle({
								fill:fill
								,stroke:stroke
								,radius:8
							})
							,fill:fill
							,stroke:stroke
						})
					})
				],
				target: 'map',
				view: new ol.View({
					center: [0, 0],
					zoom: 2
				})
			});

			[...SqS('.over',ALL),gEt('map')].map(el=>el.style.display='none');
			
			gEt('loading').classList.remove('loading-initial');
			
			// end of map stuff
			
			//events
			
			SqS('button',ONLY_ONE,gEt('searchDiv')).onclick=()=>{
				this.disabled=true;
				let wholeMap=gEt('map');
				fetch('https://nominatim.openstreetmap.org/search?city='+decodeURIComponent(gEt('ciudad').value)+'&country='+decodeURIComponent(gEt('pais').value)+'&format=json')
					.then(response=>response.json())
					.then(places=>{
						let results=gEt('citySelection');
						if(places.length){
							results.innerText='Elegí tu ciudad de la siguiente lista:';
							let setThis=function(){
								pointMapTo(this.value.split(','),12);
								let nombres=this.innerText.split(',').map(v=>v.trim()),penultimo=nombres[nombres.length-2];
								gEt('pais').value=nombres[nombres.length-1];
								gEt('provincia').value=/[^A-Z-0-9]/.test(penultimo)?penultimo:nombres[nombres.length-3];
								gEt('ciudad').value=nombres[0];
							}
							for(let city of places){
								if(city.place_id==236449628)
									continue;
								let cityButton=D.createElement('BUTTON');
								cityButton.innerText=city.display_name;
								cityButton.value=city.lon+","+city.lat;
								cityButton.onclick=setThis;
								cityButton.type='button';
								results.appendChild(cityButton);
							}
						}else results.innerText='No se ha encontrado ninguna ciudad, revise los datos e intente nuevamente.';
					})
					.catch(()=>alert('Lo sentimos, a ocurrido un error. Refresque la página y vuelva a intentarlo.'))
				//.then(/*fill citySelection*/)
					.finally(()=>this.disabled=false);//check documentation
				wholeMap.style.display='block';
			}
			
			//forms
			
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
					,ciudad:this.ciudad.value
					,direccion:this.direccion.value
					,provincia:this.provincia.value
					,pais:this.pais.value
				}
					,vendLocation=toLonLat(markerPoint.getCoordinates());
				if(!vendLocation.reduce((e,ne)=>e&&ne)){
					alert('Elija sus coordenadas');
					setFormDisabled(false);
					return false;
				}
				for(let key in data){
					data[key]=data[key].trim();
					if(!data[key]){
						alert('No debe dejar campos vacíos.');
						setFormDisabled(false);
						return false;
					}
				}
				data.location=vendLocation.join(',');
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
				<div id=searchDiv>
					<div>
						<input type="text" name=pais id=pais placeholder="Pais" required>
						<input type="text" name=provincia id=provincia placeholder="Provincia" required>
						<input type="text" name=ciudad id=ciudad placeholder="Ciudad" required>
					</div>
					<button type="button" id="searchCity">Buscar</button>
				</div>
				<input type="text" name=direccion placeholder="Dirección" style="width:70%" required>
				<div id="citySelection"></div>
			<!-- </fieldset> -->
			<div id="map"></div>
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