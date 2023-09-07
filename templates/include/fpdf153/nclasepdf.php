<?php
	include("fpdf.php");	
	
	class PDF extends FPDF
	{
		function is_utf8($str) 
		{
		    $c=0; $b=0;
		    $bits=0;
		    $len=strlen($str);
		    for($i=0; $i<$len; $i++)
		    {
		    	$c=ord($str[$i]);
		        if($c > 128)
		        {
		            if(($c >= 254)) return false;
		            elseif($c >= 252) $bits=6;
		            elseif($c >= 248) $bits=5;
		            elseif($c >= 240) $bits=4;
		            elseif($c >= 224) $bits=3;
		            elseif($c >= 192) $bits=2;
		            else return false;
		            if(($i+$bits) > $len) return false;
		            while($bits > 1)
		            {
				    	$i++;
				    	$b=ord($str[$i]);
				        if($b < 128 || $b > 191) return false;
				        	$bits--;
		            }
		        }
		    }
		    return true;
		}
		function Texto($x,$y,$cadena)
		{
			if($this->is_utf8($cadena))
				$cadena=utf8_decode($cadena);
			parent::Text($x,$y,$cadena);
		}
		function TextoF($x,$y,$cadena,$tam)
		{
			if($this->is_utf8($cadena))
				$cadena=utf8_decode($cadena);
			$aux=$this->FontSizePt;			
			parent::SetFont($this->FontFamily,"",$tam);
			parent::Text($x,$y,$cadena);
			parent::SetFont($this->FontFamily,"",$aux);
		}		
		function TexBold($x,$y,$cadena)
		{
			if($this->is_utf8($cadena))
				$cadena=utf8_decode($cadena);
			parent::SetFont($this->FontFamily,"B",$this->FontSizePt);
			parent::Text($x,$y,$cadena);
			parent::SetFont($this->FontFamily,"",$this->FontSizePt);
		}
		function TexBoldF($x,$y,$cadena,$tam)
		{
			if($this->is_utf8($cadena))
				$cadena=utf8_decode($cadena);
			$aux=$this->FontSizePt;
			parent::SetFont($this->FontFamily,"B",$tam);
			parent::Text($x,$y,$cadena);
			parent::SetFont($this->FontFamily,"",$aux);
		}
		function ya0()
		{
			$this->y=1;
			$this->x=1;
		}
		
		function obty()
		{
			return $this->y;
		}
		function celpos($xa,$ya,$ancho,$fuente,$cad,$border=0,$align="")
		{
			if($this->is_utf8($cad))
				$cad=utf8_decode($cad);
			$aux=$this->FontSizePt;
			parent::SetFont($this->FontFamily,"",$fuente);
			$alto=($fuente*0.35)/10;
			if($xa>0)
				$this->x=$xa;
			if($ya>0)
				$this->y=$ya;
			parent::MultiCell($ancho,$alto,$cad,$border,$align);
			parent::SetFont($this->FontFamily,"",$auxiliar);
		}
		function celposB($xa,$ya,$ancho,$fuente,$cad,$border=0,$align="")
		{
			if($this->is_utf8($cad))
				$cad=utf8_decode($cad);
			$aux=$this->FontSizePt;
			parent::SetFont($this->FontFamily,"B",$fuente);
			$alto=($fuente*0.35)/10;
			if($xa>0)
				$this->x=$xa;
			if($ya>0)
				$this->y=$ya;
			parent::MultiCell($ancho,$alto,$cad,$border,$align);
			parent::SetFont($this->FontFamily,"",$auxiliar);
		}
	}
?>
