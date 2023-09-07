<?php
	include( '../ajax/scriptPull.php' );
	include( '../../../../../conexionMysqli.php' );
	$sP = new scriptPull( $link );

?>
<div class="row">
	<div class="col-5">
		<h5>Rama Destino : </h5>
		<?php echo $sP->get_branch_combo( 'pull_destinity_branch' );?>
	</div>  
	<div class="col-5">
		<h5>Rama Origen : </h5>
		<?php echo $sP->get_branch_combo( 'pull_origin_branch' );?>
	</div>
	<div class="col-2">
		<br>
		<button 
			class="btn btn-success"
			onclick="comparation_between_branches();"
		>
			<i class="icon-ok-circle">Comparar</i>
		</button>
	</div>	
</div>
<br>
<div class="row scripts_list_container" style="max-height : 350px;">
	<div class="col-1"></div>
	<div class="col-10">
		<table class="table table-bordered table-striped">
			<thead  class="branch_scripts_list_header">
				<tr>
					<th>Id</th>
					<th>Descripcion</th>
					<th>Actualizar <input type="checkbox" onclick="all_scripts_selection( this );"></th>
				</tr>
			</thead>
			<tbody id="branch_scripts_pending">
			</tbody>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-4"></div>
	<div class="col-4 text-center">
		<button class="btn btn-success" id="pull_update_btn">
			<i class="icon-ok-circled">Actualizar</i>
		</button>
		<br>
		<br>
		<button class="btn btn-danger"  onclick="close_emergent();">
			<i class="icon-cancel-circled">Cancelar</i>
		</button>
	</div>
</div>

<script type="text/javascript">
	function comparation_between_branches( ){
		var origin = $( '#pull_origin_branch option:selected' ).text().toLowerCase();
		if( origin <= 0 ){
			alert( "Es necesario seleccionar la rama origen" );
			$( '#pull_origin_branch' ).focus();
			return false;
		}
		var destinity = $( '#pull_destinity_branch option:selected' ).text().toLowerCase();
		if( destinity <= 0 ){
			alert( "Es necesario seleccionar la rama destino" );
			$( '#pull_destinity_branch' ).focus();
			return false;
		}
		$.ajax({
			type : 'post',
			url : 'ajax/scriptPull.php',
			cache : false,
			data : { pull_fl : 'branches_comparation' , origin_branch_name : origin, destinity_branch_name : destinity  },
			success( dat ){
				var scripts = JSON.parse( dat );
				var resp = buildScriptList( scripts, true );
				$( '#branch_scripts_pending' ).html( resp );
				$( '#pull_update_btn' ).attr( 'onclick', `update_scripts_to_branch( '${origin}', '${destinity}' );` );
			}
		});
	}

	function all_scripts_selection( obj ){
		var checked = true;
		if( ! $( obj ).prop( 'checked' ) ){
			checked = false;
		}
		$( '#branch_scripts_pending tr' ).each( function ( index ){
			$( this ).children( 'td' ).each( function ( index2 ){
				( checked ? $( this ).children( 'input' ).prop( 'checked', true ) : $( this ).children( 'input' ).removeAttr( 'checked' ) );
			});
		});
	}
	function scripts_order_evaluation( counter ){
		var limit = ( parseInt( counter ) - 1);
		var limit_2 = $( '#branch_scripts_pending tr' ).length;
		//var limit_2 = parseInt( counter );
		//verifica scripts hacia atras
		if( $( `#script_to_update_${counter}` ).prop( 'checked' ) ){
			for (var i = limit; i >= 0; i--) {
				if( ! $( `#script_to_update_${i}` ).prop( 'checked' ) ){
					alert( "No se pueden saltar scripts!" );
					$( `#script_to_update_${counter}` ).removeAttr( 'checked' );
					return false;
				}
			}
		}
		//verifica scripts hacia adelante
		if( !$( `#script_to_update_${counter}` ).prop( 'checked' ) ){
			for (var i = counter; i < limit_2; i++) {
				if( $( `#script_to_update_${i}` ).prop( 'checked' ) ){
					alert( "No se pueden saltar scripts!" );
					$( `#script_to_update_${counter}` ).prop( 'checked', true );
					return false;
				}
			}
		}
	}
	function update_scripts_to_branch( origin, destinity ){
		var checked = '';
		var scripts_ids = '';
		$( '#branch_scripts_pending tr' ).each( function ( index ){
			checked = $( `#script_to_update_${index}` ).prop( 'checked' );
			scripts_ids += ( scripts_ids != '' && checked ? ',' : '' );
			scripts_ids += ( checked ? $( `#script_to_update_${index}` ).attr( 'value' ) : '' );
		});
		if( scripts_ids == '' ){
			alert( "No se seleccionaron cambios para actualizar rama!" );
			return false;
		} 
		$.ajax({
			type : 'post',
			url : 'ajax/scriptPull.php',
			cache : false,
			data : { pull_fl : 'update_branch_scripts' , 
					origin_branch_name : origin, 
					destinity_branch_name : destinity, 
					scripts : scripts_ids 
			},
			success( dat ){
				alert( dat );
				close_emergent();
			}
		});

	}

</script>