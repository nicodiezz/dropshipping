
var pedidos={},request=0,reclamosPageNum=0,deletingCat;

function openScreen(screenID){
	let prev=SqS('.selected');
	if(prev){
		prev.classList.remove('selected');
	}
	let opened=SqS('#realPanel > *:not(.notSelectedPanel)'),toOpen=gEt(screenID);
	if(opened)
		if(opened==toOpen)
			return;
		else opened.classList.add('notSelectedPanel');
	toOpen.classList.remove('notSelectedPanel');
	toOpen.classList.add('selected');
}

function pedirPedidos(vendedorID,from='',until=''){
	addNode(gEt('ven-pedidos'),['SPAN',{
		class:'noHayNada'
		,innerText:'Cargando...'
	}])

	let thisReques=++request;
	sendJSON('libs/ped/get-admin.php',{ID:vendedorID,from,until})
		.then(res=>res.json())
		.then(thisPedidos=>{
			pedidos[+vendedorID]={
				list:thisPedidos
				,period:[from,until]
				,cuando:+new Date
			};
			if(thisReques==request)
				addPedidos(thisPedidos);
		});
}

function addPedidos(peds){//if you ever add images, loading='lazy'
	let container=gEt('ven-pedidos');
	container.innerHTML='';
	if(peds.length){
		for(let ped of peds){
			let
				div=createNode('DIV',{
					class:'ped'
					,dataset:{id:ped.ID}
				})
				,butt=createNode('I',{
					classList:['ped-toggle','fa','fa-angle-down']
					,dataset:{value:0}
					,onclick:expand
					// ,innerText:'▽'
				})
				,span=createNode('SPAN',{
					class:'ped-name'
					,innerText:ped['titulo']
				})
				,arts=createNode('DIV',{class:'ped-arts'})
				,total=createNode('DIV',{class:'ped-plata'})
				,realTot=0;
			
			for(let titulo of ['Cant.','Artículo','Precio Unitario','Subtotal'])
				arts.append(
					createNode('DIV',{
						innerText:titulo
						,classList:['ped-arts-header']
					})
				);
			for(let art of ped.items){
				let subtNum=(+art.cantidad)*(+art['precioThen'])
					,cant=createNode('DIV',{
						innerText:art.cantidad
						,class:'ped-cantidad'
					})
					,item=createNode('DIV',{
						innerText:art.articulo
					})
					,precio=createNode('DIV',{
						innerText:+art['precioThen']
						,class:'ped-plata'
					})
					,subt=createNode('DIV',{
						class:'ped-plata'
						,innerText:+subtNum
					});
				
				realTot=normalizarNumero(realTot+subtNum);
				
				for(let info of [cant,item,precio,subt])
					arts.appendChild(info);
			}
			for(let i=0;i<3;i++){
				let blankCell=D.createElement('DIV');
				blankCell.classList.add('blank');
				arts.appendChild(blankCell);
			}
			
			total.innerText=realTot;
			arts.appendChild(total);
			
			for(let el of [butt,span,arts])
				div.appendChild(el);
			container.appendChild(div);
		}
	}else container.appendChild(createNode('SPAN',{
		innerText:'No hay pedidos con esas condiciones.'
		,class:'noHayNada'
	}));
}

function normalizarNumero(numero){
	return Math.round(numero * 1000)/1000;
}

function expand(){
	let actualValue=!!+this.dataset.value;
	this.parentNode.lastChild.style.display=actualValue?'none':'grid';
	this.dataset.value=+!actualValue;
}

function cargarMasReclamos(){
	fetch('libs/get-reclamos.php?reclamosPageNum='+reclamosPageNum++)
		.then(res=>res.json())
		.then(reclInfo=>{
			let reclamosBoton=gEt('rec-more');
			for(let reclamo of reclInfo.reclamos)//{
				reclamosBoton.before(createNode('DIV',{children:[
					['DIV',{innerText:'Fecha:'}]
					,['DIV',{innerText:reclamo.fecha}]
					,['DIV',{innerText:'Objeto:'}]
					,['DIV',{innerText:reclamo.objeto}]
					,['DIV',{
						// classList:['rec-body'],
						innerText:'\u201C'+reclamo.reclamo+'\u201D'
					}]
				]}))
			//}
			if(!reclInfo.hayMas)
				reclamosBoton.style.display='none';
		});
	
}

function lastMonthPeriod(){
	let today=(new Date).toLocaleDateString('en-CA')
		,todayExploded=today.split('-')
		,mes=todayExploded[1]-1;
	if(mes<10)
		if(mes==0)
			mes=12;
		else mes='0'+mes;
	return [todayExploded[0]+'-'+mes+'-'+todayExploded[2],today];
}

function abrirMenuGrupo(event){
	event.stopPropagation();
	if(this.classList.contains('grupo-enProceso'))
		return;
	let thisName=this.parentNode.childNodes[0].textContent.trim();
	showOptionsMessage('¿Qué desea hacer con el grupo "'+thisName+'"?'
		,['Editar',1]
		,['Eliminar',2]
		,['Cancelar',0]
	)
		.then(eleccion=>{
			switch(+eleccion){
			case 0:
				this.classList.remove('grupo-enProceso');
				break;
			case 1:
				let newName=prompt('Ingrese un nuevo nombre:',thisName);
				if(!newName || (newName=newName.trim())==thisName)
					return;
				sendJSON('libs/cat/edit.php',{newName,ID:this.dataset.id})
					.then(res=>res.text())
					.then(res=>{
						this.classList.remove('grupo-enProceso');
						if(+res)
							this.innerText=newName;
						else throw new Error('Backend answered with '+res);
					})
					.catch(e=>{
						console.log(e);
						showMessage('Ha ocurrido un error inesperado, vuelva a intentar más tarde.','red');
					});
				break;
			case 2:
				if(!confirm('¿Seguro desea borrar la categoría '+thisName+'?\nEsta acción no se puede revertir.')){
					this.classList.remove('grupo-enProceso');
					return;
				}


				jaijajauheuh
				let thisID=this.dataset.id
				sendJSON('libs/grupos/has-vends.php',{ID:thisID})
					.then(res=>res.text())
					.then(cant=>{
						if(+cant){
							deletingCat=thisID;
							selectOtherCat(cant);
						}else sendJSON('libs/grupos/delete.php',{ID:thisID,newID:0})
							.then(res=>res.text())
							.then(res=>{
								if(+res)
									this.remove();
								else throw new Error('Backend answered with '+res);
							})
							.catch(e=>{
								this.classList.remove('grupo-enProceso');
								console.log(e);
							});
					});
				break;
			}
		})
}
function abrirGrupo() {
	openScreen('grupo');
	let grupo=gEt('grupo');
	sendJSON('libs/grupos/get.php',{id:grupo.dataset.id}).then(
		res=>res.json()
	).then(
		data=>{
			SqS('h1',1,grupo).innerText=data.nombre
		}
	);
}

function selectOtherCat(cantidad){
	let holder=D.createDocumentFragment();
	for(let el of [...SqS('.categoria',ALL)]){
		if(el.dataset.id==deletingCat)
			continue;
		holder.appendChild(createNode('OPTION',{
			value:el.dataset.id
			,innerText:el.innerText
		}));
	}
	let select=gEt('cat-hasVends-body-select');
	select.innerHTML='';
	select.appendChild(holder);
	gEt('cat-hasVends-body-cuantos').innerText=cantidad+' vendedor'+(cantidad==1?'':'es');
	gEt('cat-hasVends').style.display='flex';
}

function search(queryString){
	for(let boton of gEt('ven-list-buttons').children){
		let isHidden=boton.classList.contains('hidden');
		if(test(queryString,boton.innerText)){
			if(isHidden)
				boton.classList.remove('hidden');
		}else if(!isHidden)
			boton.classList.add('hidden');
	}
}

function test(queryString,artText){
	return new RegExp('.*?'+queryString+'.*?','i').test(artText);
}

var searchID=0;

function startSearch(valor,reiniciar,buscar=true){
	if(reiniciar){
		for(let boton of [...SqS('.hidden',ALL)])
			boton.classList.remove('hidden');
		searchID++;
		return true;
	}else if(buscar){
		let myID=++searchID;
		setTimeout(()=>{
			if(searchID==myID)
				search(valor);
		},350);
	}
}

function download(URL, nombre){
	let anchor=B.appendChild(createNode('A',{
		href:URL
		,download:nombre
		,class:'hidden'
	}));
	anchor.click();
	anchor.remove();
}

addEventListener('DOMContentLoaded',()=>{
	
	//events
	
	gEt('anvorgesa').onclick=function(e){
		let target=e.target;
		if(target==this)
			this.classList.remove('anvorgesa-abierta');
		if(target.id=='salir')
			return;
		if(target.tagName=='IMG')
			target=target.parentNode;
		target=target.closest('button');
		let classList=target.classList;
		if(!classList.contains('selected')){
			let screenID;// TODO =target.value
			switch(+target.value){
			case 1:
				screenID='ven';
				break;
			case 2:
				screenID='grupos';
				break;
			case 3:
				screenID='rec';
				break;
			case 4:
				screenID='cue';
				break;
			}
			openScreen(screenID);
			this.classList.remove('anvorgesa-abierta');
		}
	}
	
	gEt('salir').onclick=async()=>{
		await fetch('libs/logout.php');
		W.location='/login.php';
	};
	
	gEt('ven-list-buttons').onclick=e=>{
		let target=e.target;
		if(target.tagName=='BUTTON'){
			let habilitado=gEt('habilitado')
				,from=gEt('from')
				,until=gEt('until');
				
			gEt('ven-nombre').innerText=target.innerText;

			// TODO Refactor: Mejorar esto...
			gEt('ven-buscar').disabled = gEt('ven-exportar').disabled = false;

			habilitado.checked=!!+target.dataset.habilitado;
			habilitado.disabled=false;
			habilitado.value=target.value;
			from.disabled=false;
			until.disabled=false;
			let thisPedido=pedidos[+target.value];
			if(!thisPedido || thisPedido < (+new Date)-3600000){
				pedirPedidos(
					target.value
					,...([from.value,until.value]=thisPedido?thisPedido.period:lastMonthPeriod())
				);
			}else{
				[from.value,until.value]=thisPedido.period;
				addPedidos(thisPedido.list);
			}
		}
	}
	
	gEt('ven-buscar').onclick=()=>{
		let checkbox=gEt('habilitado');
		if(checkbox.value!='on')
			pedirPedidos(checkbox.value,gEt('from').value,gEt('until').value);
	}
	
	gEt('ven-exportar').onclick=()=>{
		let vendedorID=gEt('habilitado').value;
		if(vendedorID!='on'){
			showOptionsMessage(
				'¿Qué desea exportar?'
				,['Todo el Historial',2]
				,['La Tabla',1]
				,['Cancelar',0]
			)
				.then(res=>{
					let IDs='';
					switch(+res){
					case 1:
						IDs=[...gEt('ven-pedidos').children].map(el=>el.dataset.id);
					case 2:
						download('libs/ped/export.php?vendedorID='+gEt('habilitado').value+'&IDs='+IDs,'pedidos.xlsx');
						break;
					}
				});
		}
	}
	
	gEt('habilitado').onchange=function(){
		this.disabled=true;
		sendJSON('libs/toggle-habilitacion.php',{vendedorID:this.value,hab:+this.checked})
			.then(res=>{
				if(!res.ok){
					this.checked=!this.checked;
					alert('Ha ocurrido un problema.');
				}else SqS('#ven-list-buttons button[value="'+this.value+'"]').dataset.habilitado = +this.checked;
				this.disabled=false;
			});
	}
	
	gEt('cue-name-update').onclick=function(){
		let input=this.previousElementSibling;
		if(input.value==input.defaultValue)
			return;
		input.disabled=true;
		let value=input.value.trim();
		sendJSON('libs/admin/update-name.php',{name:value})
			.then(res=>res.text())
			.then(response=>{
				if(+response){
					input.defaultValue=value;
					gEt('nombre').innerText=value;
				}else throw new Error('Backend said '+response);
			})
			.catch(e=>{
				console.log(e);
				showMessage('Ha ocurrido un error inesperado. Intente más tarde.','red');
			})
			.finally(()=>input.disabled=false);
	}
	
	

	gEt('cue-pass').onclick=()=>{
		let newPassword=prompt('Ingrese nueva contraseña:').trim();
		if(newPassword){
			let newPasswordAgain=prompt('Vuelva a ingresar la nueva contraseña:').trim();
			if(newPassword==newPasswordAgain){
				let oldPassword=prompt('Ingrese su contraseña actual:').trim();
				this.disabled=true;
				sendJSON('libs/admin/new-password.php',{newPassword,oldPassword})
					.then(res=>res.text())
					.then(response=>{
						switch(+response){
						case -1:
							showMessage('Ocurrió un error, intente más tarde.','#F00');
							break;
						case 0:
							showMessage('Contraseña incorrecta.','#F00');
							break;
						case 1:
							showMessage('Se ha actualizado la contraseña','#0F0');
							break;
						}
					})
					.catch(e=>{
						console.log(e);
						alert('No se ha podido realizar la acción, reintente más tarde.');
					})
					.finally(()=>this.disabled=false);
			}else showMessage('Las contraseñas ingresadas no coinciden, intentelo otra vez.');
		}
	};
	
	gEt('grupo-add').onclick=()=>{
		let nuevoNombre=prompt('Ingrese el nombre del nuevo grupo.');
		let comision=prompt('Ingrese la comision del nuevo grupo.');
		
		if(nuevoNombre && (nuevoNombre=nuevoNombre.trim())){
			let newGrupo=createNode('DIV',{
				classList:['grupo','grupo-enProceso']
				,innerText:nuevoNombre
				,onclick:abrirGrupo
			});
			gEt('grupos-container').appendChild(newGrupo);
			sendJSON('libs/grupos/add.php',{nombre:nuevoNombre,comision:comision})
				.then(res=>res.text())
				.then(res=>{
					if(+res){
						newGrupo.classList.remove('grupo-enProceso');
						newGrupo.dataset.id=res;
						showMessage('El grupo se ha creado satisfactoriamente.','limegreen');
					}else throw new Error('Backend answered with '+res);
				})
				.catch(e=>{
					console.log(e);
					newGrupo.remove();
					showMessage('Ha ocurrido un error, intente más tarde.\nSi este error le ocurre seguido, contacte al soporte.','red');
				});
		}
	}
	
	gEt('cat-hasVends').onclick=function(e){
		if(e.target==this)
			this.firstElementChild.lastElementChild.lastElementChild.click();//cancelar button
	}
	
	gEt('cat-hasVends-body-buttons').onclick=function(e){
		let target=e.target;
		if(target==this)
			return;
		
		gEt('cat-hasVends').style.display='none';
		if(+target.value){
			let categoriaToKill=SqS('.categoria[data-id="'+deletingCat+'"]');
			categoriaToKill.classList.add('grupo-enProceso');
			sendJSON('libs/cat/delete.php',{ID:deletingCat,newID:this.previousElementSibling.value})
				.then(res=>res.text())
				.then(res=>{
					if(+res)
						categoriaToKill.remove();
					else throw new Error('Backend answered with '+res);
				})
				.catch(e=>{
					categoriaToKill.classList.remove('grupo-enProceso');
					console.log(e);
					showMessage('Ha ocurrido un error, intente de nuevo más tarde.\nSi ocurre seguido, contacte con el soporte.','red');
				});
		}
		deletingCat=null;
	}
	
	let listFilter=gEt('ven-list-filter')
	
	listFilter.onkeyup=function(e){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue && e.which==8
			,e.key.length==1 || e.which==8
		);
	};
	
	listFilter.onchange=function(){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue
		);
	};
	
	//UI building
	
	fetch('libs/get-vendedores.php')
		.then(res=>res.json())
		.then(vends=>{
			if(vends.length){
				let container=gEt('ven-list-buttons');
				for(let vend of vends)
					container.appendChild(createNode('BUTTON',{
						value:vend['ID']
						,innerText:vend['nombre']
						,dataset:{habilitado:vend['habilitado']}
					}));
			}
		});
		
	gEt('rec').append(createNode('BUTTON',{
		id:'rec-more'
		,innerText:'Cargar más reclamos'
		,onclick:cargarMasReclamos
	}));
	
	cargarMasReclamos();
	
	fetch('libs/grupos/get-all.php')
		.then(res=>res.json())
		.then(grupos=>{
			let container=gEt('grupos-container');
			if(grupos.length){
				for(let grupo of grupos){
					let grupo_div = createNode('DIV',{
						class:'grupo'
						,innerText:grupo['nombre']
						,onclick:abrirGrupo
						,dataset:{id:grupo['ID']}
					});
					grupo_div.appendChild(createNode('BUTTON',{
						class:"grupo-edit",
						onclick:abrirMenuGrupo,
						innerText:'⋮',
						dataset:{id:grupo['ID']}
					}));
					container.appendChild(grupo_div);
				}
			}else{
				container.innerHTML='<p>No existen grupos de artículos aún.</p>';
			}
		})
	
});