
<script type="text/javascript" src="../js/jquery-1.10.2.min.js"></script>
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
		var curpData, 
			nameData, 
			lastName1Data, 
			lastName2Data, 
			dateOfBirthData, 
			DateOfIncorporationDateData,
			situationData,
			lastSituationChangeData;
		var globalData = new Array();
		/*$( '#final_data_0 tr' ).each( function ( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				if ( index2 % 2 != 0)
				globalData.push( $( this ).html().trim() );
			});
		} );*/
	$( '.ui-datatable-data.ui-widget-content' ).each( function ( index ){
		globalData[index] = new Array();
		$( this ).children( 'tr' ).each( function ( index2 ){
			$( this ).children( 'td' ).each( function ( index3 ){
				//alert( $(this).html() );
				//if( index3 % 2 != 0 ){
					globalData[index].push( $(this).html() );
				//}
			});	
		});
	});
	setTimeout( function(){}, 3000 );
		console.log( globalData );
		window.opener.build_content( globalData );
	}
</script>

<script type="text/javascript">
	getIdentificationData();
</script>