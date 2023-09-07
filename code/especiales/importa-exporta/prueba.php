<?php
	extract($_POST);
?>
<style>
	.vista{
		width:95%;
		height: 400px;
		border:1px solid;
		overflow: auto;
	}
	.bot{
		padding: 10px;
		border-radius: 8px;
	}
</style>
<center>
<form action="prueba.php" method="POST" id="form1">
	<input type="file" name="archivo" value="Seleccionar Archivo">
	<input type="button" value="Vista Previa" onclick="cargar();" class="bot"/>
</form>
<div class="vista">
<?php
if(isset($archivo)){
	$page = file_get_contents($archivo);
	echo utf8_encode($page);
}
?>
</div>
<form style="display: hidden" action="ajax/generaCSV.php" method="POST" id="formulario">
	<input type="hidden" id="datos" name="datos" value="">
	<input type="button" value="Exportar en CSV" onclick="exportaDo();" class="bot">
</form>
</center>
<script type="text/JavaScript">
//aqui exportamos
	function exportaDo(){
	var data="";
	var td,objIn,objIn2,objIn3,objIn4,objIn5,tp,axFecha,fecha;
	//obtenemos las tablas existentes
		var tabla=document.getElementsByTagName('table');
	//obtenemos tabla de datos que nos interesa y fijamos el topñe del for
		var tope=tabla[1].getElementsByTagName('tr').length;
		var tr=tabla[1].getElementsByTagName('tr');
		for(var i=1;i<tope;i++){
			td=tr[i].getElementsByTagName('td');
			//fecha
				objIn=td[0].getElementsByTagName('span');
				axFecha=objIn[0].innerHTML.split("/");
				fecha=axFecha[2]+"/"+axFecha[1]+"/"+axFecha[0];
				//alert(fecha);
			//hora
				objIn2=td[8].getElementsByTagName('span');
				var aux=objIn2[0].innerHTML;
				//alert(aux);
			//monto
				objIn3=td[6].getElementsByTagName('span');
			//folio
				objIn4=td[3].getElementsByTagName('span');
			//tipo de pago
				objIn5=td[4].getElementsByTagName('span');
				if(objIn5[0].innerHTML=='DEBITO'){
					tp=14;
				}else if(objIn5[0].innerHTML=='CREDITO'){
					tp=11;
				}

				//objIn3=td[6].getElementsByTagName('span');
			data+=parseInt(objIn4[0].innerHTML)+"|";//id
			data+=fecha+"|";
			data+=aux[0]+""+aux[1]+":"+aux[2]+""+aux[3]+":"+aux[4]+""+aux[5]+"|";//hora-fecha         			
			data+=parseInt(objIn3[0].innerHTML)+"|";
			data+=parseInt(objIn4[0].innerHTML)+"|Pagado|Mostrador|SI|"+tp;
			//}
			if(i<tope-1){
				data+="~";
			}
		}
		//alert(data);
	//enviamos datos al archivogenerador de CSV
		if(document.getElementById('datos').value=data){
			if(document.getElementById("formulario").submit()){

			}
		}

	}
//cargamos archivo
	function cargar(){
		document.getElementById("form1").submit();
	}
</script>