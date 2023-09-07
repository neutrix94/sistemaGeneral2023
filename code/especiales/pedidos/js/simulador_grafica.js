var simulador_en_uso=0;
	function simulador_tooltip(obj,id_sucursal){
		var coordenadas = $(obj).position();//obtenemos coordenadas
		if(simulador_en_uso==1){
				esconde_tooltip();
				return true;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/tooltipEstacionalidades.php',
			cache:false,
			data:{id_suc_:id_sucursal},
			success:function(dat){
				$("#simula_tooltip").html(dat);//cargamos los datos
				//$("#simula_tooltip").offset({top: parseInt(coordenadas.top+70), left:parseInt(coordenadas.left)+275});//asignamos coordenadas
				//$("#simula_tooltip").offset({top: 50, left:650});//asignamos coordenadas
				$("#simula_tooltip").css("display","block");//hacemos visible la simulacion del tooltip
				$("#simula_tooltip").css("position","fixed");
				$("#simula_tooltip").css("top","14%");
				$("#simula_tooltip").css("right","0");
				
				simulador_en_uso=1;
			}
		});
	}
	function esconde_tooltip(obj){
		//$("#simula_tooltip").offset({top:0,left:0});//reseteamos coordenadas
		$("#simula_tooltip").css("display","none");
		simulador_en_uso=0;
	}

	function graficar_inv_vtas(id_prod, num){
		//fecha_max_del=$('#fcha_max_del').val();
		if(fecha_max_del==''||fecha_max_del==null){/*
			alert("La fecha inicial no puede ir vacía!!!");
			$("#fcha_max_del").focus();*/
			carga_filtros_prom(id_prod,0);//,'grafica'
			return false;
		}
		//fecha_max_al=$('#fcha_max_al').val();
		if(fecha_max_al==''||fecha_max_al==null){/*
			alert("La fecha final no puede ir vacía!!!");
			$("#fcha_max_al").focus();*/
			carga_filtros_prom(id_prod,0);//,'grafica'
			return false;
		}
	//enviamos datos por ajax
		
		$.ajax({
			type:'post',
			url:'ajax/grafica_2.php',
			cache:false,
			data:{id_prodcto:id_prod,fcha_del:fecha_max_del,fcha_al:fecha_max_al},
			success:function(dat){
//alert(dat);
				$("#simula_tooltip_grafica").html("");
				$("#simula_tooltip_grafica").html(dat + '<p align="center">'
					+ '<button onclick="next_graphic( ' + num + ', -1);"><img src="../../../img/grid/first.png"></button>'
					+ '<button onclick="next_graphic( ' + num + ', 1);"><img src="../../../img/grid/last.png"></button></p>');
				$("#simula_tooltip_grafica").css('display','block');

				setTimeout( function(){
					$( '.highcharts-series-2' ).click();

					},300
				);
			}
		});
	}

	function next_graphic( num, value ){
		if( num == 1 && value == -1 )
			return false;
		$( '#grafica_btn_' + ( parseInt( num ) + parseInt( value) ) ).click();
	}

	function colorea(num){
		$("#fil_gr_"+num).css("background","rgba(0,0,225,.5)");
	}
	function descolorea(num){
		$("#fil_gr_"+num).css("background","#FFF8BB");
	}
	function detalle_sin_inventario(id,sucursal){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/getInventarioInsuficiente.php',
			cache:false,
			data:{id_prd:id,id_suc:sucursal,fcha_del:fecha_max_del,fcha_al:fecha_max_al},
			success:function(dat){
				$("#subemergente").html(dat);
				$("#subemergente").css("display","block");
			}
		});
	}