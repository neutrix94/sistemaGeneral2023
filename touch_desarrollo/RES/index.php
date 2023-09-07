<?php

    include("../conectMin.php");
    
    if (session_start()) {
    	die ("Error al inicializar la sessión");
    }

    require ("includes/header-principal.php");
    
    
    
?>
<?php require ("includes/menu-secc.php");?>

  <?php require ("includes/contenido0.php");?>

<?php require ("includes/footer.php");?>

        