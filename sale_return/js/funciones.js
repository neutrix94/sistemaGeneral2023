/*
 * 
 *  Funciones JavaScript
 *  
 *  Invocarse despues de cargar jQuey
 * 
 * */

String.prototype.repeat = function( num ) { return new Array( num + 1 ).join( this ); };
String.prototype.left = function( num ) { return this.substring (0, num); };


function moneyFormat (nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0].length > 0 ? x[0] : '0';
	if (typeof x[1] == typeof undefined) {x2 = '.00';}
	else {x[1] = x[1].left(2); x2 = '.' + x[1] + "0".repeat (2-x[1].length);}
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

// Numeric only control handler 
jQuery.fn.ForceNumericOnly = function() {
    return this.each(function() {
        $(this).keydown(function(e) {
            var key = e.charCode || e.keyCode || 0;

            // allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
            // home, end, period, and numpad decimal 
            return (
                key == 8 || 
                key == 9 ||
                key == 46 ||
                key == 110 ||
                key == 173 ||
                key == 109 ||
                key == 190 ||
                (key >= 35 && key <= 40) ||
                (key >= 48 && key <= 57) ||
                (key >= 96 && key <= 105));
        });
    });
};
