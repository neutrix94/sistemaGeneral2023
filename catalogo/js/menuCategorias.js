/*Buscador*/
	function buscar_prds(e,obj,flag){
		var texto=$("#buscador").val().trim();//alert(texto);
		if(flag!=1){
			if(e.keyCode==40){//tecla_abajo
				$("#opc_res_1").focus();
				return false;
			}
		}
		if(texto.length<=2){
			$("#res_busc").html('');
			$("#res_busc").css("display","none");
			return false;
		}

		$.ajax({
			type:'post',
			url:'ajax/buscador_prds.php',
			cache:false,
			data:{dato:texto},
			success:function(dat){
				aux=dat.split("|||");
				$("#res_busc").html(aux[0]);
				$("#res_busc").css("display","block");
				if(flag==1){
					$("#contenido").html(aux[1]);
				}
			}
		});
	}

	function carga_prd_busc(flag,id_prd){
		location.href="productos.php?cHJvZHVjdG8="+id_prd;
	}

	function valida_tca_opc(e,num){
		if(e.keyCode==38){//tecla arriba
			if(num==1){
				$("#buscador").select();
			}else{
				$("#opc_res_"+(parseInt(num-1))).focus();
			}
			return false;
		}
		if(e.keyCode==40){//tecla abajo
			$("#buscador").select();
			$("#opc_res_"+(parseInt(num+1))).focus();
			return false;
		}
	}

	function resalta(flag,obj){
		if(flag==1){
			$(obj).css("background","rgba(0,225,0,.5)");
		}
		if(flag==0){
			$(obj).css("background","white");
		}
	}

/**/

/**/
function recorre_categorias(){
	var categorias="";
	var tope=$("#tope_categorias").val();
//	alert(tope);
	for(var i=0;i<=tope;i++){
		if($("#chk_"+i).prop("checked")==true){
			categorias+=$("#chk_"+i).val()+"~";
		}
	}
	var url="productos.php?";
	if($("#array_familias").val()!=''){
		url+="cat="+$("#array_familias").val();
	}
	
	//if($("#array_subcategorias").val()!=''){
	//	url+="&subcat="+$("#array_subcategorias").val()+categorias;	
	//}else{
		url+="&subcat="+categorias;
		url+="&suc="+$("#id_sucu").val();//docuemnt.getElementById('id_sucu').val();
	//}

	location.href=url;
//alert(categorias);
}

function carga_categorias(){
	var tope=$("#tope_categorias").val();
	var categorias="";
	for(var i=0;i<=tope;i++){
		if($("#chk_fam_"+i).prop("checked")==true){
			categorias+=$("#chk_fam_"+i).val()+"~";
		}
	}
	if(categorias==""){
		alert("Debe de seleccionar al menos una familia para continuar!!!");
		$("#chk_fam_1").focus();
		return false;
	}
	location.href="productos.php?cat="+categorias;
}
	function colorea_familias(num){
		if($("#chk_fam_"+num).prop("checked")==true){
			$("#familia_"+num).css("background","rgba(0,200,0,0.3");
		}

		if($("#chk_fam_"+num).prop("checked")==false){
			$("#familia_"+num).css("background","transparent");
		}
	}