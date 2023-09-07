<?php

// include database and object files
require "../classes/database.php";
include_once '../classes/codigoSAT.php';
// for pagination purposes
$page = isset($_GET['page']) ? $_GET['page'] : 1; // page is the current page, if there's nothing set, default is page 1
$id_categoria = isset($_GET['id_categoria']) ? $_GET['id_categoria'] : null;
$id_subcategoria = isset($_GET['id_subcategoria']) ? $_GET['id_subcategoria'] : null;
$id_categoria = isset($_POST['id_categoria']) ? $_POST['id_categoria'] : $id_categoria;
$id_subcategoria = isset($_POST['id_subcategoria']) ? $_POST['id_subcategoria'] : $id_subcategoria;

$records_per_page = 10; // set records or rows of data per page
$from_record_num = ($records_per_page * $page) - $records_per_page; // calculate for the query limit clause

// instantiate database and sat object
$sat = new CodigoSAT();
$sat->id_categoria =$id_categoria;
$sat->id_subcategoria =$id_subcategoria;

// include header file
$page_title = "Administración códigos SAT";
include_once "header.php";

echo '
<nav class="navbar navbar-default navbar-fixed-top" style="background-color: #b10015;">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="../../index.php" style="color: #fff">
        Casa de las Luces
      </a>
    </div>
  </div>
</nav>';
// create user button
//echo "<div class='right-button-margin'>";
echo "<div class='row'>
      <form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?page=1' method='post'>
        <div class='col-sm-4'>
          <label>Familia</label>
          <select class='form-control' name='id_categoria'>
          <option value='0'> - Selecciona Familia - </option>";
$stmt = $sat->getFamilia();
$selected = '';
while ($row = $stmt->fetch()) {
    $selected = ($id_categoria==$row['id_categoria']) ? 'selected' : '';
    echo "<option value='$row[id_categoria]' {$selected}>$row[nombre_categoria]</option>";
}
echo "    </select>
        </div>
        <div class='col-sm-4'>
          <label>Tipo</label>
          <select class='form-control' name='id_subcategoria'>
          <option value='0'> - Selecciona Tipo - </option>";
$stmt = $sat->getTipo();
$selected = '';
while ($row = $stmt->fetch()) {
    $selected = ($id_subcategoria==$row['id_subcategoria']) ? 'selected' : '';
    echo "<option value='$row[id_subcategoria]' {$selected}>$row[nombre_subcategoria]</option>";
}
echo "    </select>
        </div>
        <div class='col-sm-4'>
        <br>
          <button type='submit' class='btn btn-primary' >
              <span class=''></span> Filtrar
          </button>
        </div>
      </form>
    </div><br>";
// echo "<a href='create.php' class='btn btn-primary pull-right'>";
// echo "<span class='glyphicon glyphicon-plus'></span> Filtrar";
// echo "</a>";
// echo "</div>";

// select all users
$stmt = $sat->getAllSatCode($from_record_num, $records_per_page,$id_categoria, $id_subcategoria); //Name of the PHP variable to bind to the SQL statement parameter.
$num=1;

// check if more than 0 record found
if($num>=0){

    echo "<table class='table table-hover table-responsive table-bordered'>";
    echo "<tr style='background-color: #1e282a; color: #e6efec;'>";
    echo "<th>Familia</th>";
    echo "<th>Tipo</th>";
    echo "<th>Código SAT</th>";
    echo "<th>Descripción SAT</th>";
    echo "<th>Descripción CL</th>";
    echo "<th>Acciones</th>";
    echo "</tr>";
    while ($row = $stmt->fetch()) {

        // extract($row); //Import variables into the current symbol table from an array
        echo "<tr>";
        echo "<td>$row[nombre_categoria]</td>";
        echo "<td>$row[nombre_subcategoria]</td>";
        echo "<td>$row[codigo_sat]</td>";
        echo "<td>$row[descripcion_sat]</td>";
        echo "<td>$row[descripcion_cl]</td>";

        echo "<td>";
        // edit user button
        echo "<a href='edit.php?id=" . $row['id_subcategoria'] . "&filtro_categoria={$id_categoria}&filtro_subcategoria={$id_subcategoria}' class='btn btn-info right-margin'>";
        echo "<span class='glyphicon glyphicon-edit'></span> Editar";
        echo "</a>";

        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";

    // include pagination file
    include_once 'pagination.php';
}

// if there are no user
else{
    echo "<div> No se encuentran resultados. </div>";
    }
?>


<?php
include_once "footer.php";
?>
