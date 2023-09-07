<?php
	switch($reporte){
/***************************************INVENTARIO*******************************************/
//Catalogo de Clientes
		case 1:
			$arrayTablas = array('ec_pedidos'=>'Pedidos', 'ec_clientes' => 'Clientes');
		break;
//Cat�logo de Facturas
		case 2:
			$arrayTablas = array('ec_pedidos'=>'Pedidos','ec_clientes'=>'Clientes','ec_guias'=>'Gu&iacute;as');
		break;
//Facturas por Tipo de Cliente
		case 3:
			$arrayTablas = array('ec_pedidos'=>'Pedidos', 'ec_productos' => 'Productos');
		break;
//Facturas por Concepto
		case 4:
			$arrayTablas = array();
		break;
//Prefacturas (sin Sello Digital)
		case 5:
			$arrayTablas = array('ec_pedidos'=>'Pedidos');
		break;
//Cat�logo de Notas de Cr�dito
		case 6:
			$arrayTablas = array('ec_pedidos'=>'Pedidos','ec_tipos_pago'=>'Tipo de pago');
		break;
//Notas de Cr�dito por Tipo de Cliente
		case 7:
			$arrayTablas = array('sac_pedidos'=>'Pedidos','sac_prospectos'=>'Clientes', 'sac_estados'=>'Estados');
		break;
//Notas de Cr�dito por Concepto
		case 8:
			$arrayTablas = array('ec_cuentas_por_cobrar'=>'Cuentas x Cobrar', 'ec_pedidos'=>'Pedidos', 'ec_clientes' => 'Clientes');
		break;
//PreNotas de Cr�dito (sin Sello Digital)
		case 9:
			$arrayTablas = array();
		break;
		case 10;
			$arrayTablas = array();
		break;
		case 11:
			$arrayTablas = array();
		break;
		case 12:
			$arrayTablas = array();
		break;	
		case 13:
			$arrayTablas = array();	
		break;				
		case 14:			
			$arrayTablas = array('novalaser_notas_venta'=>'Notas de Venta','novalaser_sucursales'=>'Sucursales', 'novalaser_clientes'=>'Clientes');			
		break;				
		case 15:			
			$arrayTablas = array('novalaser_notas_venta'=>'Notas de Venta','novalaser_servicios'=>'Servicios', 'novalaser_servicios_tipos'=>'Tipos de servicio');						
		break;				
		case 16:			
			$arrayTablas = array('novalaser_sucursales'=>'Sucursales','novalaser_notas_venta'=>'Notas de Venta');			
		break;
		case 18:
			$arrayTablas = array('ec_pedidos'=>'Pedidos', 'ec_clientes' => 'Clientes','ec_tipos_pago'=>'Tipo de pago');
		break;

/***************************************EXPORTACION DE ASISTENTES*******************************************/		
		default:
			$arrayTablas = array();
	}
?>