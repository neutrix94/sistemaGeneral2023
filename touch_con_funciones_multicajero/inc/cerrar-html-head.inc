	<script type="text/javascript">
	/* <![CDATA[ */
	
		function eliminarItem (item) {
	    	  $(item).parents ("tr.move").first ().remove ();
	    	  calculaTotal ();
	    	  $("#monto").focus ();
	      }

		function calculaTotal () {
	    	// Sumar el total 
		    total = 0.0;
		    $("#listaPagos tr.move td.tabla_monto").each (function(index, value) {
			    total += parseFloat($(this).html ().replace (/[\$\s,]/g, ""));
			});
		    $("#listaPagos tr.total td.tabla_total").html ("$ " + moneyFormat(total));
		}

		function agregarMonto () {
			if ($("#monto").val ().match (/^-?\d+(?:\.\d*)?$/i)) {
				var existe = false;
				$("#listaPagos tr.move input.id_tipo").each (function(index, value) {
					if ($(this).val () == $("#tipo_pago").val ()) {
						var monto = parseFloat($(this).parents("tr.move").first ().find ("td.tabla_monto").html ().replace (/[\$\s,]/g, ""));
						monto = monto + parseFloat ($("#monto").val ());
						$(this).parents("tr.move").first ().find ("td.tabla_monto").html ("$ " + moneyFormat(monto));
						calculaTotal ();
						existe = true;
					}
				});

				if (!existe) {
					if (parseFloat($("#monto").val ()) > 0) $("<tr class=\"move\"><td class=\"tabla_tipo_pago\">" + $("#tipo_pago option:selected").text() + "</td><td class=\"tabla_monto\">$ " + moneyFormat($("#monto").val ()) + "</td><td><a href=\"javascript:void(0)\" onclick=\"eliminarItem(this)\" class=\"eliminar\"> <span>eliminar</span></a><input type=\"hidden\"  class=\"id_tipo\"  value=\""+ $("#tipo_pago").val() + "\" /></td></tr>").insertBefore ($("#listaPagos tr.total"));
					else alert ("Introduzca una cantidad positiva mayor a cero.");
					calculaTotal ();
				} 
			} else {
				alert ("El monto capturado es inválido");
			}

			$("#monto").val ("");
			$("#monto").focus ();
		}

		function cerrarVenta () {

			var retval = false; 
			var es_apartado = $("#es_apartado").prop ("checked") ? "1" : "0";
			var id_pedido = "<?php echo $id_pedido; ?>";

			var datos = "nitems=" + $("#listaPagos tr.move").length;
			$("#listaPagos tr.move").each (function(index, value) {
				datos += "&idt" + index + "=" + $(this).find ("input.id_tipo").val () + "&mon" + index + "=" + $(this).find ("td.tabla_monto").html ().replace (/[\$\s,]/g, "");
			});

			$.ajax({
				async: false,
				type: 'GET',
				url: "ajax/cerrarVenta.php?idp=" + id_pedido + "&ap=" + es_apartado + "&" + datos
			}).done (function (data) {
				retval = data.match (/^ok$/i);
			}).fail (function () {
				retval = false;
			});

			return retval;
		}

		function changeHashOnLoad() {
			var base_href = location.href.match (/^([^\#]*)(?:\#.*)?$/i)[0];
	        location.href = base_href + "#";
	        setTimeout("changeHashAgain()", "50");
	    }

	    function changeHashAgain() {          
	        location.href += "1";
	    }

	    var storedHash = window.location.hash;
	    setInterval(function () {
	        if (location.hash != storedHash) {
	            location.hash = storedHash;
	        }
	    }, 50);
	      
		$(document).ready(function() {

			// Bloquear evento goBack () 
			changeHashOnLoad();

			$("#monto").on ("keydown", function (e) {
		    	var key = e.charCode || e.keyCode || 0;
		    	if (key == 13) {
			    	agregarMonto();
			    	return false;
		    	}
		    });
			$("#monto").ForceNumericOnly();

			$("#agregar").on ("click", function () {
				agregarMonto();
			});

			/*$("#regresar").on ("click", function () {
				document.location.href = "index.php?scr=nueva&idp=<?php echo $id_pedido; ?>";
				return false;
			});*/

			$("#cerrar").on ("click", function () {
				

				$('#cerrar').attr('disabled','-1')
			    //alert('OK 1');
				if (Math.abs (parseFloat ($("#listaPagos tr.total td.tabla_total").html ().replace (/[\$\s,]/g, "")) - parseFloat ($("#total_orden").val ().replace (/[\$\s,]/g, ""))) <= 1)
				{
					
					//alert('OK 2');
					// Venta 
					if (cerrarVenta ())
					{
					    //alert('OK 3');
					    location.href = "index.php";
						//alert('Ok 4');
						
						setTimeout('location.href = "index.php"', 2000);  
					}
					else
					{
						alert ("Error al intentar el almacenado de los datos.\nVerifique conectividad e intente nuevamente.");
					}
				}
				else if ($("#es_apartado").prop ("checked"))
				{
					// Apartado 
					if (cerrarVenta ())
					{
					     
						location.href = "index.php";
					}
					else
					{
						alert ("Error al intentar el almacenado de los datos.\nVerifique conectividad e intente nuevamente.");
					}
				}
				else
				{
					// Error 
					alert ("Imposible continuar.\nLos totales difieren.");
					$('#cerrar').removeAttr('disabled')
					return false;
				}
				
				$('#cerrar').removeAttr('disabled')
			});

			$("#cancelar").on ("click", function () {
				if (confirm ("¿Cancelar la venta?")) {
				    $.ajax({
				    	async: false,
		        	     type: 'GET',
				        url: 'ajax/cancelarVenta.php?idp=<?php echo $id_pedido; ?>'
				    }).done(function (source) {
					    document.location.href = "index.php";
					}).fail (function () {
						// No se pudo cancelar la venta 
						// alert ("Error al cancelar la venta."); 
					});
					return false;
				} else {
					return false;
				}
			});

			$("#es_apartado").on ("click", function () {
				if ($("#es_apartado").prop ("checked")) {
					// Abrir diálogo de datos para el cliente 
					//$("#cliente_nombre, #cliente_telefono, #cliente_movil, #cliente_email").val (""); 
					$("#divApartado").css ("display", "block");
				}
			});

			$("#guardar_apartado").on ("click", function () {
				var es_ok = true;
				
				var falta_nom=0;
				var falta_tel=0;
				// Validar que el usuario haya capturado datos reales 
				if (!$("#cliente_nombre").val ().replace (/[\s\-\(\)]/g, "").length)  { falta_nom=1;}
				if (!$("#cliente_telefono").val ().replace (/[\s\-\(\)]/g, "").match (/\d{8,}/i))  { falta_tel++;}
				if (!$("#cliente_movil").val ().replace (/[\s\-\(\)]/g, "").match (/\d{8,}/i))  { falta_tel++; }
				if (!$("#cliente_email").val ().match (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/))  {  }
				
				if(falta_nom == 1 || falta_tel == 2)
				    es_ok=false;
				

				if (!es_ok) {
					// Alguna validación detectó una falla con la captura 
					alert ("Introduzca adecuadamente los datos del cliente.\n Es requerido el nombre, y al menos un telefono");
					return false;
				} else {
					// Se permite el almacenado  
					
					
					$.ajax({
                        async: false,
                         type: 'GET',
                        url: 'ajax/guardaCliente.php?idp=<?php echo $id_pedido; ?>&nombre='+$("#cliente_nombre").val ()+'&telefono='+$("#cliente_telefono").val ()+"&celular="+$("#cliente_movil").val ()+"&correo="+$("#cliente_email").val ()
                    }).done(function (source) {
                       $("#divApartado").css ("display", "none");
                    }).fail (function () {
                        // No se pudo cancelar la venta 
                        alert ("Error al guardar al cliente."); 
                    });
                    return false;
					
				}
			});

			$("#cancelar_apartado").on ("click", function () {
				$("#divApartado").css ("display", "none");
				$("#es_apartado").prop ("checked", false);
			});

			$("#monto").focus ();
		});  
	/* ]]> */
	</script>
