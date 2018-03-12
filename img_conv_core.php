<?php

$img_file = $_FILES["img_file"]["tmp_name"];
$output_name = $_POST["name"];
$transp = $_POST["transp"];
$format = $_POST["format"];

$w = 0;
$h = 0;
$size = getimagesize($img_file);
$w = $size[0];
$h = $size[1];


$ext = pathinfo($_FILES["img_file"]["name"], PATHINFO_EXTENSION);
if($ext == "png") $img = imagecreatefrompng($img_file);
else if($ext == "bmp") $img = imagecreatefrombmp($img_file);
else if($ext == "jpg") $img = imagecreatefromjpeg($img_file);
else if($ext == "jpeg") $img = imagecreatefromjpeg($img_file);
else echo("$ext is a not supported image type. use png, jpg, jpeg or bmp");

if($format == "c_array") conv_c_src();
else conv_bin_rgb();



function conv_c_src()
{
    global $w;
    global $h;
    global $output_name;
    global $img;
    global $transp;

    $c_src .= "#include <stdint.h>
#include \"lv_conf.h\"
#include \"lvgl/lv_draw/lv_draw.h\"

static const uint8_t " . $output_name . "_pixel_map[] = {\n";

    $c_src8 = "\n#if LV_COLOR_DEPTH == 1 || LV_COLOR_DEPTH == 8\n";
    if ($transp != "alpha") $c_src8 .= "/*Pixel format: Red: 3 bit, Green: 3 bit, Blue: 2 bit*/\n";
    if ($transp == "alpha")  $c_src8 .= "/*Pixel format: Alpha 8 bit, Red: 3 bit, Green: 3 bit, Blue: 2 bit*/\n"; 

    $c_src16 = "\n\n#elif LV_COLOR_DEPTH == 16\n";
    if ($transp != "alpha") $c_src16 .= "/*Pixel format: Red: 5 bit, Green: 6 bit, Blue: 5 bit*/\n";
    if ($transp == "alpha")  $c_src16 .= "/*Pixel format: Alpha 8 bit, Red: 5 bit, Green: 6 bit, Blue: 5 bit*/\n"; 

    $c_src24 = "\n\n#elif LV_COLOR_DEPTH == 24\n";
    if ($transp != "alpha") $c_src24 .= "/*Pixel format: Fix 0xFF: 8 bit, Red: 8 bit, Green: 8 bit, Blue: 8 bit*/\n";
    if ($transp == "alpha")  $c_src24 .= "/*Pixel format: Alpha 8 bit, Red: 8 bit, Green: 8 bit, Blue: 8 bit*/\n"; 

    $a_str = "";

    for($y = 0; $y < $h; $y++) {
	    $c_src8 .= "\n  ";
	    $c_src16 .= "\n  ";
	    $c_src24 .= "\n  ";

	    for($x = 0; $x < $w; $x++) {
		    $c = imagecolorat($img, $x, $y);
		    if($transp == "alpha") {
		        $a = ($c & 0xff000000) >> 23;       /*Alpha is 7 bit*/
		        if($a & 0x02) $a |= 0x01;           /*Repeate the last bit: 0000000 -> 00000000; 1111110 -> 11111111*/
		        $a = 255 - $a;
		        $a_str = "0x" . str_pad(dechex($a), 2, '0', STR_PAD_LEFT) . ", ";
		    }
		
		    $r = ($c & 0x00ff0000) >> 16;
		    $g = ($c & 0x0000ff00) >> 8;
		    $b = ($c & 0x000000ff) >> 0;
		
		    $c8 = ($r & 0xE0) | (($g & 0xE0) >> 3) | ($b >> 6);	//RGB332
		    $c_src8 .= "0x" . str_pad(dechex($c8), 2, '0', STR_PAD_LEFT). ", ";
		    $c_src8 .= $a_str;	
			
		    $c16 = (($r & 0xF8) << 8) | (($g & 0xFC) << 3) | (($b & 0xF8) >> 3);	//RGR565
		    $c_src16 .= "0x" . str_pad(dechex(($c16 & 0x00FF)), 2, '0', STR_PAD_LEFT). ", ";
		    $c_src16 .= "0x" . str_pad(dechex(($c16 & 0xFF00) >> 8), 2, '0', STR_PAD_LEFT). ", ";
		    $c_src16 .= $a_str;
		
		    $c24 = ($r << 16) | ($g << 8) | ($b);	//RGR888
		    $c_src24 .= "0x" . str_pad(dechex(($c24 & 0x0000FF)), 2, '0', STR_PAD_LEFT). ", ";
		    $c_src24 .= "0x" . str_pad(dechex(($c24 & 0x00FF00) >> 8), 2, '0', STR_PAD_LEFT). ", ";
		    $c_src24 .= "0x" . str_pad(dechex(($c24 & 0xFF0000) >> 16), 2, '0', STR_PAD_LEFT). ", ";
		    if($transp == "alpha") $c_src24 .= $a_str;
		    else $c_src24 .= "0xff, ";  /*Padding*/
	    }
	
    }    

    $c_src .= $c_src8;
    $c_src .= $c_src16;
    $c_src .= $c_src24;

    $c_src .= "

    #else
    #error \"$output_name " . "image :invalid color depth (check LV_COLOR_DEPTH in lv_conf.h)\"
    #endif
    };\n\n";


    $c_src .= "
const lv_img_t $output_name = {
  .header.w = $w,\t\t\t/*Image width in pixel count*/	
  .header.h = $h,\t\t\t/*Image height in pixel count*/\n";	
    if($transp == "alpha") $c_src .= "  .header.alpha_byte = 1,\t\t/*Alpha byte added to every pixel*/\n";  
    else  $c_src .= "  .header.alpha_byte = 0,\t\t/*No alpha byte*/\n";  

    if($transp == "chroma") $c_src .= "  .header.chroma_keyed = 1,\t/*LV_COLOR_TRANSP (lv_conf.h) pixels will be transparent*/\n";  
    else  $c_src .= "  .header.chroma_keyed = 0,\t/*No chroma keying*/\n";
    $c_src .= "  .header.format = LV_IMG_FORMAT_INTERNAL_RAW,\t/*It's a variable compiled into the code*/\n";
    $c_src .= "  .pixel_map = " . $output_name . "_pixel_map\t/*Pointer the array of image pixels.*/
};\n\n";
    
    $output_name .= ".c";
    download($output_name, $c_src);
    
}

function conv_bin_rgb()
{
    global $w;
    global $h;
    global $output_name;
    global $img;
    global $transp;
    global $format;

    $header = pack("v", $w << 20) | pack("v", $h << 8);
    //echo($transp . "<br><br><br><br>");
    if($transp == "chroma")  $chroma = 1;
    if($transp == "alpha")  $alpha = 1;

    if($format == "bin_rgb332") $img_format = 2;
    if($format == "bin_rgb565") $img_format = 3;
    if($format == "bin_rgb888") $img_format = 4;
     
    $header = pack("V", $chroma | $alpha << 1 | $img_format << 2 | $w << 8 | $h << 20);
    
    $bin = $header;
    
    $a = 255;

    for($y = 0; $y < $h; $y++) {
	    for($x = 0; $x < $w; $x++) {
		    $c = imagecolorat($img, $x, $y);
		    
		
		    $r = ($c & 0x00ff0000) >> 16;
		    $g = ($c & 0x0000ff00) >> 8;
		    $b = ($c & 0x000000ff) >> 0;
	        if($transp == "alpha") {
		        $a = ($c & 0xff000000) >> 23;       /*Alpha is 7 bit*/
		        if($a & 0x02) $a |= 0x01;           /*Repeate the last bit: 0000000 -> 00000000; 1111110 -> 11111111*/
		        $a = 255 - $a;
		    }
		
		    if($format == "bin_rgb332") {
		        $c8 = ($r & 0xE0) | (($g & 0xE0) >> 3) | ($b >> 6);	//RGB332
		        $bin .= pack("C", $c8);
		        if($transp == "alpha") $bin .= pack("C", $a);
		    } else if($format == "bin_rgb565") {
		        $c16 = (($r & 0xF8) << 8) | (($g & 0xFC) << 3) | (($b & 0xF8) >> 3);	//RGR565
		        $bin .= pack("v", $c16);
		        if($transp == "alpha") $bin .= pack("C", $a);
	        } else if($format == "bin_rgb888") {
		        $c24 = ($a << 24) | ($r << 16) | ($g << 8) | ($b);	//RGR888
		        $bin .= pack("V", $c24);
	        }
	    }
    }    
    
    $output_name .= ".bin";
    download($output_name, $bin);
    
}



function download($name, $content)
{
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    header('Content-Type: application/text');
    header('Content-disposition: attachment; filename='.$name);
    header('Content-Length: ' . strlen($content));
    echo($content);
    
    
}

?>

