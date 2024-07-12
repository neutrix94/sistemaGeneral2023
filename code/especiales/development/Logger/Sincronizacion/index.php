<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<link rel="stylesheet" href="css/styles.css">
<script src="../../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<div class="emergent">
    <div class="emergent_content"></div>
</div>
<div class="row header bg-warning">
    <div class="col-6">
        Buscar por folio unico
        <div class="input-group">
            <input type="text" class="form-control" id="folio_input">
            <button
                type="button"
                class="btn btn-success"
                onclick="filtra_por_folio('#folio_input');"
            >
                Buscar
            </button>
        </div>
    </div>
    <div class="col-6">
        Filtrar por tablas<br>
        <?php
            if( !include( '../../../../../conexionMysqli.php' ) ){
                die( "../../../../../conexionMysqli.php" );
            }
            if( !include( 'ajax/LoggerViewer.php' ) ){
                die( "ajax/LoggerViewer.php" );
            }
            $LoggerViewer = new LoggerViewer( $link );
            echo $LoggerViewer->getTables();
        ?>
    </div>
</div>
<div class="content">
    <?php
        echo $LoggerViewer->getLoggerRows();
    ?>
</div>