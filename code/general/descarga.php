<?php
        
        extract($_GET);
        
        $file=str_replace("https://easycount.com.mx/billarmex//", "../../", $file);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        //header("Content-type: atachment-download");
        //header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        //header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        //header("Content-type: atachment/vnd.ms-excel");
        header("Content-type: application/xls");
        
        
        header("Content-Disposition: atachment; filename=\"productos.csv\";");
        header("Content-transfer-encoding: binary\n");
        
        
        $ar=fopen($file, "rt");
        if($ar)
        {
            while(!feof($ar))
            {
                $aux=fgets($ar, 10000);
                echo $aux;
            }
        }
        fclose($ar);

?>