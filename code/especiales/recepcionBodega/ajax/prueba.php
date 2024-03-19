<?php
$letra = 'BG';

while ($letra <= 'ZZZZ') {
    echo $letra . "<br>";
    $letra = siguienteLetra($letra);
}

function siguienteLetra($letra) {
    // Convierte la letra en un número base 26 (A=0, B=1, ..., Z=25)
    $numero = 0;
    $len = strlen($letra);
    for ($i = 0; $i < $len; $i++) {
        $numero = $numero * 26 + ord($letra[$i]) - ord('A');
    }
    
    // Incrementa el número
    $numero++;
    
    // Convierte el número de nuevo en una letra
    $nuevaLetra = '';
    while ($numero > 0) {
        $remainder = $numero % 26;
        $nuevaLetra = chr($remainder + ord('A')) . $nuevaLetra;
        $numero = intval($numero / 26);
    }
    
    return $nuevaLetra;
}
?>