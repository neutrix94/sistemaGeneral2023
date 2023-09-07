<?php
  include('Barcode.php');
  include("../../include/fpdf153/nclasepdf.php");
  
  
  
  
  // -------------------------------------------------- //
  //                      USEFULL
  // -------------------------------------------------- //
  
  class eFPDF extends FPDF{
    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
    {
        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;
    
        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);
    
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
  }

  // -------------------------------------------------- //
  //                  PROPERTIES
  // -------------------------------------------------- //
  
  $fontSize = 10;
  $marge    = 10;   // between barcode and hri in pixel
  $x        = 200;  // barcode center
  $y        = 200;  // barcode center
  $height   = 50;   // barcode height in 1D ; module size in 2D
  $width    = 2;    // barcode height in 1D ; not use in 2D
  $angle    = 45;   // rotation in degrees : nb : non horizontable barcode might not be usable because of pixelisation
  
  $code     = '123456789012'; // barcode, of course ;)
  $type     = 'ean13';
  $black    = '000000'; // color in hexa
  
  
  // -------------------------------------------------- //
  //            ALLOCATE FPDF RESSOURCE
  // -------------------------------------------------- //
    
  //$pdf = new eFPDF('P', 'pt');
  
  $pdf=new PDF("P","pt",array(193,95));
  
  $pdf->AddPage();
  
  // -------------------------------------------------- //
  //                      BARCODE
  // -------------------------------------------------- //
  
  $data = Barcode::fpdf($pdf, $black, 96.5, 40, 0, $type, array('code'=>$code), 1.8, 60);
  
  
  
  //$pdf->celpos(2, 3, 10, $ftam, $nombre,0,"L");
  
  // -------------------------------------------------- //
  //                      HRI
  // -------------------------------------------------- //
  
  $pdf->SetFont('Arial','B',$fontSize);
  $pdf->SetTextColor(0, 0, 0);
  $len = $pdf->GetStringWidth($data['hri']);
  
  $pdf->Cell(10,10,'Estamos viendo',1,0,'C');
  
  
  //Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, 0.1, $xt, $yt);
  //$pdf->TextWithRotation($x + $xt, $y + $yt, $data['hri'], 0.1);
  //$pdf->celpos(10, 5, 20, 4, $nombre,0,"L");
  
  $pdf->Output();
?>