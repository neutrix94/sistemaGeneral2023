	
	function focus_password(){
		$( '#password' ).focus();
	}

	function link(flag){
		if(flag==1){
			if(confirm("Realmente desea regresar al panel?")==true){
				location.href=("../../../../index.php");
			}
		}
	}
//funcion que valida login
	function abrir_caja(){
	//validamos datos
	var log,contra, cambio;
	log=$("#user").val();
	if(log.length<=0){
		alert("El campo de cajero no puede ir vacío!!!");
		$("#user").focus();
		return false;
	}
	contra=$("#password").val();
	if(contra.length<=0){
		alert("La contraseña de cajero no puede ir vacía!!!");
		$("#password").focus();
		return false;
	}
	cambio=$("#cambio_caja").val();
	if(cambio.length<=0){
		alert("El cambio inicial en caja es obligatorio*");
		$("#cambio_caja").focus();
		return false;
	}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'abreSesionCaja.php',
			cache:false,
			data:{
				login:log,
				contrasena:contra,
				cambio_caja : cambio	
			},
			success:function(dat){
				if(dat!='ok'){
					alert(dat);
					//location.reload();
				}else{
					alert("Sesión de caja iniciada exitosamente!!!");
					location.href='../../../../index.php?';
				}
			}
			});
	}

	function pending_sales_validation(){
	//envia detos por ajax
		$.ajax({
			type : 'post',
			url : '../../Reportes/ajax/pending_validation_tkt.php',
			cache : false,
			data : { flag : 'seek_pending_to_validate' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					alert(dat);return false;
				}else{
					if( aux[1] != '' && aux[1] != null ){
						$( "#contenido_emergente" ).html( aux[1] );
						$( "#emergente" ).css( "display", "block" );
					}	
				}
			}		
		});
	}

	function print_pending_ticket(){
		cierra_emergente();
		$.ajax({
			type : 'post',
			url : '../../Reportes/ajax/pending_validation_tkt.php',
			cache : false,
			data : { flag : 'print_pending_to_validate' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					alert(dat);return false;
				}else{
					$( "#contenido_emergente" ).html( '' );
					$( "#emergente" ).css( "display", "none" );
					//$( "#contenido_emergente" ).append( `` );
				}
			}		
		});
	}

	function cierra_emergente(){
		$("#contenido_emergente").html('');
		$("#emergente").css("display","none");	
	}