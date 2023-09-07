// JavaScript Document
YAHOO.namespace( 'YAHOO.Hack' ).FixIESelectWidth = new function()
{
	var oSelf = this; 
	var YUE = YAHOO.util.Event;
	var YUD = YAHOO.util.Dom;
	var oTimer = {};
	var oAnim = {};
	var nTimerId =  0 ;
	var dLastFocalItem;
	var ie7 = !!(document.uniqueID  &&   typeof(XMLHttpRequest)!='undefined' )
	function init(el)
	{
		
		
		el = el || this;
		
		

		if( el.tagName.toLowerCase() != 'select')
		{
			throw Error('element [' + el.id + '] is not <select>');
			return;
		};	
		
		if(!YUD.hasClass( el.parentNode, 'select-box'))
		{
			throw Error('className select-box is not included for element [' + el.id + ']');
			return;
		};	
		
		var oRs = el.runtimeStyle;
		var oPRs = el.parentNode.runtimeStyle;
		
		
		oPRs.fonSize = 0;
		
		
		var sDisplay = el.parentNode.currentStyle.display.toLowerCase() ;
		if(  sDisplay=='' ||  sDisplay=='inline' ||  sDisplay=='inline-block' )
		{
			oPRs.display = 'inline-block';
			oPRs.width = el.offsetWidth + 'px';
			oPRs.height =el.offsetHeight + 'px';
			oPRs.position = 'relative';
			oRs.position = 'absolute';
			oRs.top = 0;
			oRs.left = 0;
		};
		
		
		
		el._timerId = ( nTimerId+=1 );

		el.selectedIndex = Math.max( 0 , el.selectedIndex );
		
		oTimer[ '_' + el._timerId ] = setTimeout('void(0)',0);
		oAnim [ 'A' + el._timerId ] = setTimeout('void(0)',0);
		
		YUE.on( el, 'mouseover' , onMouseOver);
		YUE.on( document, 'mousedown' ,onMouseDown , el, true);
		YUE.on(  el, 'change' ,collapseSelect , el, true);
	}


	function collapseSelect(e)
	{
		status++;
		this.runtimeStyle.width = '';			
	}

	function onMouseOver(e )
	{
	
		var el = this;	
		if(dLastFocalItem && dLastFocalItem !=el)
		{
			 onMouseDown.call( dLastFocalItem , e );
		};

		var sTimerId ='_' +  el._timerId ;
		var sAniId = 'A' + el._timerId ;
		clearTimeout( oTimer[ sTimerId ] );

		

		var onTween = function()
		{
			clearTimeout( oAnim [  sAniId  ] );
			if( Math.abs( nEndWidth - nStartWidth ) > 3 )
			{
				nStartWidth += (nEndWidth - nStartWidth ) /3;
				el.runtimeStyle.width = nStartWidth + 'px';
				oAnim [  sAniId  ] = setTimeout( onTween ,0 );
			}
			else
			{
				el.runtimeStyle.width = 'auto';
				el.selectedIndex = Math.max( 0 , el.selectedIndex );
			}
		}

		var nStartWidth =  el.offsetWidth ;
		el.runtimeStyle.width = 'auto';
		var nEndWidth  = el.offsetWidth;
		

		clearTimeout( oAnim [  sAniId  ] );
		onTween();

		el.focus();		
		dLastFocalItem = el;
	}

	function onMouseDown(e , el )
	{
		el = ( e.srcElement || e.target );
		
		
		
		if( el == this && e.type!='mouseover' )
		{
			status++;
			YUE.stopEvent(e);
			return false;
		};
		
		
		el = this;
		
		clearTimeout( oAnim [ 'A' + el._timerId ] );
	
		
		var sTimerId ='_' +  el._timerId ;
		var doItLater = function()
		{
			el.runtimeStyle.width = '';			
		};
		if( e.type=='mouseover')
		{ doItLater();}
		else{
			oTimer[ sTimerId ] = setTimeout(doItLater,100);
		}
	}

	

	function constructor(sId)
	{
		sId = [ sId , ''].join('');
		//Only fix for IE55 ~ IE7
		
		if(document.uniqueID && window.createPopup )
		{			
			YUE.onAvailable(sId ,init );
			return true;

		}else{return false};
	};

	return  constructor;
}