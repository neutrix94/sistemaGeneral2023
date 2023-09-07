<!-- Librerias para el grid-->
  <script language="javascript" src="RedCatGrid.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/fix.js"></script>
  <script type="text/javascript" src="../js/calendar.js"></script>
  <script type="text/javascript" src="../js/calendar-es.js"></script>
  <script type="text/javascript" src="../js/calendar-setup.js"></script>
  
 <!--link rel="stylesheet" type="text/css" href="../css/grid_touch_nuevo.css"/-->
     <script src="js/plugins.js"></script>
    <script src="js/funciones.js"></script>
    
<!-- SCRIPTS HEAD-->

<script type="text/javascript">
	/* <![CDATA[ */
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
    }, 250);
    
		$(document).ready(function() {
			// Bloquear evento goBack () 
			changeHashOnLoad();
		});
	/* ]]> */
</script>
<style type="text/css">
  td input[type=button] {
    border-radius: none;
    background: #0dcaf0 !important;
    border: none;
    color: #fff;
    /*padding: 6px;*/
    padding-bottom: 10px;
    font-size: 120%;
  }
  .Contador {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt;
    font-weight: bold;
    background-repeat: repeat;
    color: #FFF;
    background: #0dcaf0;
    width: 36px !important;
    text-align: center;
  }
  .list_container{
    width: 100% !important;
    max-width: 100% !important;
    overflow: auto;
  }
  /*td{
    display: block;
  }*/
</style>