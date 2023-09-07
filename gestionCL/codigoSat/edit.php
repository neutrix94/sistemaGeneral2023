<?php
// set page headers
$page_title = "Editar código";
include_once "header.php";
$filtro_categoria = isset($_GET['filtro_categoria']) ? $_GET['filtro_categoria'] : 0 ;
$filtro_subcategoria = isset($_GET['filtro_subcategoria']) ? $_GET['filtro_subcategoria'] : 0 ;
$filter = "";
$filter = ($filtro_categoria!=0) ? $filter."&id_categoria={$filtro_categoria}" : $filter;
$filter = ($filtro_subcategoria!=0) ? $filter."&id_subcategoria={$filtro_subcategoria}" : $filter;

echo '
<nav class="navbar navbar-default navbar-fixed-top" style="background-color: #b10015;">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="../../index.php" style="color: #fff">Casa de las Luces</a>
    </div>
  </div>
</nav>';

// read user button
echo "<div class='right-button-margin'>";
    echo "<a href='index.php?page=1{$filter}' class='btn btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-list-alt'></span> Volver ";
    echo "</a>";
echo "</div>";

// isset() is a PHP function used to verify if ID is there or not
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR! ID not found!');

// include database and object user file
include_once '../classes/database.php';
include_once '../classes/codigoSAT.php';
//include_once 'initial.php';

// instantiate user object
// $user = new User($db);
// $user->id = $id;
// $user->getUser();

$sat = new CodigoSAT();
$sat->id = $id;
$sat->getCodigoSat();


// check if the form is submitted
if($_POST)
{

    // set user property values
    $sat->id_categoria = $_POST['id_categoria'];
    $sat->id_subcategoria = $_POST['id_subcategoria'];
    $sat->nombre_categoria = $_POST['nombre_categoria'];
    $sat->nombre_subcategoria = $_POST['nombre_subcategoria'];
    $sat->codigo_sat = $_POST['codigo_sat'];
    $sat->descripcion_sat = $_POST['descripcion_sat'];
    $sat->descripcion_cl = $_POST['descripcion_cl'];

    // Edit user
    if($sat->updateCodigoSat()){
        echo "<div class=\"alert alert-success\">";
            echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">
                        &times;
                  </button>";
            echo "Éxito! Se han guardado los cambios.";
        echo "</div>";
    }else{
        echo "<div class=\"alert alert-danger alert-dismissable\">";
            echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">
                        &times;
                  </button>";
            echo "Error! No se ha podido actualizar.";
        echo "</div>";
    }
}
?>

    <!-- Bootstrap Form for updating a user -->
    <form action='edit.php?id=<?php echo $id; ?>&filtro_categoria=<?php echo $filtro_categoria; ?>&filtro_subcategoria=<?php echo $filtro_subcategoria; ?>' method='post'>

        <table class='table table-hover table-responsive table-bordered'>

            <tr>
                <td>Familia</td>
                <td>
                  <input type='text' name='nombre_categoria' value='<?php echo $sat->nombre_categoria;?>' class='form-control' style="pointer-events:none" >
                  <input type='text' name='id_categoria' value='<?php echo $sat->id_categoria;?>' style="display:none">

                </td>
            </tr>

            <tr>
                <td>Tipo</td>
                <td>
                  <input type='text' name='nombre_subcategoria' value='<?php echo $sat->nombre_subcategoria;?>' class='form-control' style="pointer-events:none">
                  <input type='text' name='id_subcategoria' value='<?php echo $sat->id_subcategoria;?>' style="display:none">
                </td>
            </tr>

            <tr>
                <td>Código SAT </td>
                <td><input type='text' name='codigo_sat' value='<?php echo $sat->codigo_sat;?>' class='form-control' placeholder="Ingresa Código SAT" required></td>
            </tr>

            <tr>
                <td>Descripción SAT</td>
                <td><input type='text' name='descripcion_sat' value='<?php echo $sat->descripcion_sat;?>' class='form-control' placeholder="Ingresa Descripción SAT" required></td>
            </tr>
            <tr>
                <td>Descripción CL</td>
                <td><input type='text' name='descripcion_cl' value='<?php echo $sat->descripcion_cl;?>' class='form-control' placeholder="Ingresa Descripción CL" required></td>
            </tr>


            <tr>
                <td></td>
                <td>
                    <button type="submit" class="btn btn-success" >
                        <span class=""></span> Guardar
                    </button>
                </td>
            </tr>

        </table>
    </form>

<?php
include_once "footer.php";
?>
