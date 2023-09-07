<?php
/*
	sucursal_id
	user_id
*/
	include('../../../../conexionMysqli.php');
	$usuario = $_POST['user_id'];

	include('consultaDB.php');

	$eje = $link->query( $sql ) or die( "Error al buscar registros de nomina : {$link->error}");

	$format = new formatDateUtils();

	while ( $payrolls = $eje->fetch_row() ) {
		echo build_row( $payrolls, $format );
	}

	function build_row( $payrolls, $format ){
		static $count = 0;
		static $global_count = 0;
		static $descuentos = 0;
		$payroll = explode('|', $payrolls[3]);
		$resp = '';
		//foreach ($payroll as $key => $row) {
		$salary = 0;
		$total_hours = 0;
		for ( $i = 0; $i < ( sizeof($payroll) > 2 ? sizeof($payroll) : 2 ); $i++ ){
			$cout_day = 0;
			$payroll_dates = explode('~', $payroll[$i]);
			$payrolls[2] = date('Y-m-d', strtotime($payrolls[2]) );

			$resp .= "<tr class=\"table-" . ( $count % 2 == 0 ? 'primary' : 'secondary' )  ."\">";
				
				$resp .= "<td style=\"display : none;\">{$format->date_to_text( $payroll_dates[2]) }</td>";
				
				$resp .= "<td>{$payrolls[1]}</td>";
				
				$resp .= "<td onclick=\"edit_row( this, 'date' );\" 
							id=\"{$global_count}_1\">{$payrolls[2]}</td>";
			
				$resp .= "<td onclick=\"edit_row( this, 'time' );\" 
							id=\"{$global_count}_2\">" . ($payroll_dates[0] ? $payroll_dates[0] : '00:00:00') . 
						"</td>";
			
				$resp .= "<td onclick=\"edit_row( this, 'time' );\" 
							id=\"{$global_count}_3\">" . ($payroll_dates[1] ? $payroll_dates[1] : '00:00:00') . 
						"</td>";
			
				$resp .= "<td>" . ($payroll_dates[3] ? $payroll_dates[3] : '00:00:00') . 
						"</td>";
				//$resp .= "<td>{$payrolls[5]}</td>";
			//inasistencia
				$resp .= "<td class=\"checks\"><b class=\"label_check\">Inasistencia</b> <br /><input type=\"checkbox\">Justificado<br /><input type=\"checkbox\" "
					. (  $count_dates == 0 && $payroll_dates[0] ? '' : 'checked' )
					. "> No justificado</td>";
			//retardo
				$resp .= "<td class=\"checks\"><b class=\"label_check\">Retardo</b> <br /><input type=\"checkbox\">Justificado<br /><input type=\"checkbox\" " . (  $count_dates == 0 && $payroll_dates[0] > $payroll[5] ? '' : 'checked' ) . "> No justificado</td>";
			//no checó
				$resp .= "<td class=\"checks\"><b class=\"label_check\">No checó</b> <br /><input type=\"checkbox\">Justificado<br /><input type=\"checkbox\"> No justificado</td>";
			
			$resp .= "</tr>";
			$global_count ++;
		}
		$count ++;
		return $resp;
	}

	/**
	* 
	*/
	class formatDateUtils
	{
		private $days = Array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
		private $months = Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
		'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

		function __construct($argument){

		}

	//conversion de fecha a texto
		function date_to_text( $date ){
			$format_date;
			/*$date = explode('-', $date);
			$date = implode('/', $date);*/
			$original_date = date_format( $date );
			//$format_date = $days[original_date.getDay()];
			/*format_date += ' ' + original_date.getDate();
			format_date += ' de ' + months[original_date.getMonth()];
			format_date += ' de ' + original_date.getFullYear();*/
			//alert( format_date );
			return $format_date;
		}

	//conversion de texto a fecha
		function text_to_date( $text ){
			/*var date_format;
			var tmp = text.split(' ');
			date_format = ( tmp[1] <= 9 ? '0' + tmp[1] : tmp[1] ) + '/';
			date_format += months.indexOf( tmp[3] ) + '/';
			date_format += tmp[5];
			alert( date_format );
			return date_format;*/
		}

	}
?>