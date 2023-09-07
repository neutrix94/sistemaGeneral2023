<html>
	<!--script type="text/javascript" src="../js/jquery-1.10.2.min.js"></script-->
	<script type="text/javascript" src="cleaner.js"></script><head>
	<title></title>
</head>
<body>
		<div class="row">
			<div class="col-2">
			</div>
			<div class="col-10">
				<div class="input-group">
					<input type="text" class="form-control" id="url_value" placeholder="Ingresa la url">
					<button type="button" class="btn btn-primary" id="get_data_btn" onclick="getDataSat();">
						Consultar
					</button>
				</div>
			</div>
		</div>
		<div id="response_container">
			
		</div>


</body>
</html>
<script type="text/javascript">
	var global_popout = 0;
	function getDataSat( url ){
			var url = $( '#url_value' ).val().trim();//'https://siat.sat.gob.mx/app/qr/faces/pages/mobile/validadorqr.jsf?D1=10&D2=1&D3=16050344931_HELC720716ME6';
			if( url.length <= 0 ){
				alert( "Es necesario ingresar una url para continuar!" );
				$( '#url_value' ).focus();
				return false;
			}
		//	url = 'https://siat.sat.gob.mx/app/qr/faces/pages/mobile/validadorqr.jsf?D1=10&D2=1&D3=20090196414_CLB200805GW0';
			var response = ajaxR( url );
			//alert( response );
			processData( response);
	}

	function processData( data ){
		data = clean_data( data );
	//	alert( data );
		ajaxR_write(data);
		//openProceesData( data );
	}

	function getIdentificationData(){
		var curpData, 
			nameData, 
			lastName1Data, 
			lastName2Data, 
			dateOfBirthData, 
			DateOfIncorporationDateData,
			situationData,
			lastSituationChangeData;
		var globalData = new Array();
		$( '#ubicacionForm:j_idt11:0:j_idt12:j_idt16_data tr' ).each( function ( index ){
			$( this ).children( 'tr' ).each( function(){
				globalData.push( $( this ).html().trim() );
			});
		} );
		console.log( globalData );
	}

	function openProceesData( data ){
		var url_ = "popout.php?file=" + data ;
		global_popout = window.open( url_, 'Template', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=10,height=10,left=0,top=0');
		
		//global_popout.getElementById( 'pageContent' ).innerHTML = '<button type="button">Here</button>' ;
	}
/**/
	function build_content( info ){
		var resp = `<br>`;
		for ( var i = 0; i < info.length ; i++ ) {
			if( info[i].length > 0 ){
				resp += `<table class="table table-striped">`;
				var info_final = info[i];
				for ( var j = 0; j < info_final.length ; j++ ) {
					resp += `<tr><td>${info_final[j]}</td>`;
					j ++;
					resp += `<td>${info_final[j]}</td></tr>`;
				}
				resp += `</table><br>`;
			}
		}
		$( '#response_container' ).html( resp );
		global_popout.close();
	}
/**/


	//llamadas asincronas

	function ajaxR_write(data){
		$.ajax({
			type : 'post',
			data : { data : data },
			url : 'saveContent.php',
			cache : false,
			success : function(dat){
			//	alert(dat);
				openProceesData( dat );
			} 
		});
	}
	function ajaxR(url){
	/*	$.ajax({
			type : 'post',
			data : { data : data },
			cache : false,
			success : function(){

			} 
		});*/

		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}



</script>

		<script type="text/javascript">
			//getData();
		</script>