
	var days = new Array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
	
	var months = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
	'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

//conversion de fecha a texto
	function date_to_text( date ){
		var format_date;
		date = date.split('-').join('/');
		const original_date = new Date( date );
		format_date = days[original_date.getDay()];
		format_date += ' ' + original_date.getDate();
		format_date += ' de ' + months[original_date.getMonth()];
		format_date += ' de ' + original_date.getFullYear();
		//alert( format_date );
		return format_date;
	}

//conversion de texto a fecha
	function text_to_date( text ){
		var date_format;
		var tmp = text.split(' ');
		date_format = ( tmp[1] <= 9 ? '0' + tmp[1] : tmp[1] ) + '/';
		date_format += months.indexOf( tmp[3] ) + '/';
		date_format += tmp[5];
		alert( date_format );
		return date_format;
	}