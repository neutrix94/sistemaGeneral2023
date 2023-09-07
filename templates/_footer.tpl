<div id="linea"></div>
<!--<table id="footer" border="0">
    <td><img src="{$rooturl}img/easycount.png"/></td>
    <td id="desarrollado"><p>Desarrollado por: <a href="http://terminus.mx/" target="_blank">T&eacute;rminus MX</a></p></td>

</table>-->

<footer>
    <div class="w-footer">
      <div class="secc-footer">
    	
    </div>
     <div class="secc-footer-der">
	<!--<img src="{$rooturl}img/logoeasy.png" width="80" height="34" title="Se autoriza el uso de este producto a: Casa de las luces"/><br>
    <p id="desarrollado"><span class="white">Desarrollado por:</span> <a class="link-footer" href="http://www.terminus10.com/"><strong>T&eacute;rminus<img class="diez" src="{$rooturl}img/numero_terminus.png" width="13" height="18" alt=""/></strong></a></p>-->
    </div>
    </div>
</footer>

<script type="text/javascript">
	{literal}

	//var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
	
	function cierraSesion()
	{
		if(confirm("\xBFRealmente desea salir de la sesion actual?")){
		//implementacion Oscar 2023 para borrar el sesion storage
        	sessionStorage.clear();
			{/literal}
			location.href="{$rooturl}index.php?cierraSesion=YES";
			{literal}
		}
	}
	
	{/literal}
</script>
</body>
</html>