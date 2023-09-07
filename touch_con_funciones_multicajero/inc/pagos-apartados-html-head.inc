<!-- Librerias para el grid-->
  <script language="javascript" src="RedCatGrid.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/fix.js"></script>
  <script type="text/javascript" src="../js/calendar.js"></script>
  <script type="text/javascript" src="../js/calendar-es.js"></script>
  <script type="text/javascript" src="../js/calendar-setup.js"></script>
  </style>  
 <link rel="stylesheet" type="text/css" href="../css/grid_touch_nuevo.css"/>
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
