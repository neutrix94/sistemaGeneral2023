
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<?php
	$name = $_GET['file'];	
	$file = fopen("{$name}", "r");
	while(!feof($file)) {

		echo fgets($file);

	}

	fclose($file);
?>

<!--button onclick="getIdentificationData();">
	here
</button-->

<script type="text/javascript">
	function getIdentificationData(){
		var rfc,
			curpData, 
			nameData, 
			lastName1Data, 
			lastName2Data, 
			dateOfBirthData, 
			DateOfIncorporationDateData,
			situationData,
			lastSituationChangeData;
		var globalData = new Array();
		//window.opener.getElementById( 'costumer_name_input' ).value = `${nameData} ${lastName1Data} ${lastName2Data}`//build_content( globalData );
		/*$( '#final_data_0 tr' ).each( function ( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				if ( index2 % 2 != 0)
				globalData.push( $( this ).html().trim() );
			});
		} );*/
		//alert();
		var tmp_rfc = "";
		//alert( $( "#ubicacionForm:j_idt10" ).html() );
		$( 'li' ).each( function(){
			//alert( $(this).html() );
			tmp_rfc = $(this).html().trim().split( ' ' );
			return false;
		});
		rfc = tmp_rfc[2].replace( ',', '' );
		//alert( rfc );
//1	$( '.ui-datatable-data.ui-widget-content' ).each( function ( index ){
	$( '.ui-datatable-data.ui-widget-content' ).each( function ( index ){
		globalData[index] = new Array();
		$( this ).children( 'tr' ).each( function ( index2 ){
			$( this ).children( 'td' ).each( function ( index3 ){
				//alert( $(this).html() );
				//if( index3 % 2 != 0 ){
					//alert( $(this).html() );
					globalData[index].push( $(this).html() );
					//if( index3 == 1 ){
						//alert($(this).html());
						//window.costumer_name_input = $(this).html();//build_content( globalData );
					//}
				//}
			});	
		});
	});
	setTimeout( function(){}, 3000 );
		console.log( globalData );
		window.opener.build_content( globalData, rfc );//
	}
</script>

<script type="text/javascript">
	getIdentificationData();
</script>