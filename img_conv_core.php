<?php
$offline = 0;
if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_POST);
  $offline = 1;
}

if($offline == 0){
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  $img_file = $_FILES["img_file"]["tmp_name"];
  $img_file_name = $_FILES["img_file"]["name"];
  $output_name = $_POST["name"];
  $transp = $_POST["transp"];
  $format = $_POST["format"];
  $dith = $_POST["dith"];
}
else{
  if(isset($_POST["name"])){
    $output_name = $_POST["name"];
  }
  else{
    echo("Mising Name\n");
    exit(0);
  }

  if(isset($_POST["img"])){
    $img_file = $_POST["img"];
    $img_file_name = $_POST["img"];
  }
  else{
    echo("Mising image file\n");
    exit(0);
  }

  if(isset($_POST["format"])){
    $format = $_POST["format"];
  }
  else{
    $format = "c_array";
  }

  if(isset($_POST["transp"])){
    $transp = $_POST["transp"];
  }
  else{
    $transp = "none";
  }

  if(isset($_POST["dith"])){
    $dith = $_POST["dith"];
  }
  else {
    $dith = "enabled";
  }
}

$w = 0;
$h = 0;
$size = getimagesize($img_file);
$w = $size[0];
$h = $size[1];

$ext = pathinfo($img_file_name, PATHINFO_EXTENSION);
if(!strcmp($ext, "png")) $img = imagecreatefrompng($img_file);
else if(!strcmp($ext, "bmp")) $img = imagecreatefrombmp($img_file);
else if(!strcmp($ext, "jpg")) $img = imagecreatefromjpeg($img_file);
else if(!strcmp($ext == "jpeg")) $img = imagecreatefromjpeg($img_file);
else echo("$ext is a not supported image type. use png, jpg, jpeg or bmp");

if(!strcmp($format, "c_array")) conv_c_src();
else conv_bin_rgb();

function conv_c_src(){
  global $w;
  global $h;
  global $output_name;
  global $img;
  global $transp;
  global $c_src;
  global $dith;

  $r_332_earr = array();  // Classification error for next row of pixels
  $g_332_earr = array();
  $b_332_earr = array();
  $r_565_earr = array();
  $g_565_earr = array();
  $b_565_earr = array();
  $r_888_earr = array();
  $g_888_earr = array();
  $b_888_earr = array();

  $r_332_nerr = 0;  // Classification error for next pixel
  $g_332_nerr = 0;
  $b_332_nerr = 0;
  $r_565_nerr = 0;
  $g_565_nerr = 0;
  $b_565_nerr = 0;
  $r_888_nerr = 0;
  $g_888_nerr = 0;
  $b_888_nerr = 0;

  if(!strcmp($dith, "enabled")){
    for($i = 0; $i < $w + 2; ++$i){
      $r_332_earr[$i] = 0;
      $g_332_earr[$i] = 0;
      $b_332_earr[$i] = 0;
      $r_565_earr[$i] = 0;
      $g_565_earr[$i] = 0;
      $b_565_earr[$i] = 0;
      $r_888_earr[$i] = 0;
      $g_888_earr[$i] = 0;
      $b_888_earr[$i] = 0;
    }
  }

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

  for($y = 0; $y < $h; $y++){
    $c_src8 .= "\n  ";
    $c_src16 .= "\n  ";
    $c_src24 .= "\n  ";

    if(!strcmp($dith, "enabled")){
      $r_332_nerr = 0;
      $g_332_nerr = 0;
      $b_332_nerr = 0;
      $r_565_nerr = 0;
      $g_565_nerr = 0;
      $b_565_nerr = 0;
      $r_888_nerr = 0;
      $g_888_nerr = 0;
      $b_888_nerr = 0;
    }

    for($x = 0; $x < $w; ++$x){
      $c = imagecolorat($img, $x, $y);
        if($transp == "alpha"){
          $a = ($c & 0xff000000) >> 23;       /*Alpha is 7 bit*/
          if($a & 0x02) $a |= 0x01;           /*Repeate the last bit: 0000000 -> 00000000; 1111110 -> 11111111*/
            $a = 255 - $a;
            $a_str = "0x" . str_pad(dechex($a), 2, '0', STR_PAD_LEFT) . ", ";
        }

      $r = ($c & 0x00ff0000) >> 16;
      $g = ($c & 0x0000ff00) >> 8;
      $b = ($c & 0x000000ff) >> 0;

        /* Conversion for RGB332 */

      if(!strcmp($dith, "enabled")){
        $r_332 = $r + $r_332_nerr + $r_332_earr[$x+1];
        $r_332_earr[$x+1] = 0;
        $g_332 = $g + $g_332_nerr + $g_332_earr[$x+1];
        $g_332_earr[$x+1] = 0;
        $b_332 = $b + $b_332_nerr + $b_332_earr[$x+1];
        $b_332_earr[$x+1] = 0;

        $r_332 = classifyPixel($r_332, 3);
        $g_332 = classifyPixel($g_332, 3);
        $b_332 = classifyPixel($b_332, 2);

        if($r_332 < 0) $r_332 = 0;   if($r_332 > 0xE0) $r_332 = 0xE0;
        if($g_332 < 0) $g_332 = 0;   if($g_332 > 0xE0) $g_332 = 0xE0;
        if($b_332 < 0) $b_332 = 0;   if($b_332 > 0xC0) $b_332 = 0xC0;

        $r_332_err = $r - $r_332;
        $g_332_err = $g - $g_332;
        $b_332_err = $b - $b_332;

        $r_332_nerr = round((7 * $r_332_err) / 16);
        $g_332_nerr = round((7 * $g_332_err) / 16);
        $b_332_nerr = round((7 * $b_332_err) / 16);

        $r_332_earr[$x] += round((3 * $r_332_err) / 16);
        $g_332_earr[$x] += round((3 * $g_332_err) / 16);
        $b_332_earr[$x] += round((3 * $b_332_err) / 16);

        $r_332_earr[$x+1] += round((5 * $r_332_err) / 16);
        $g_332_earr[$x+1] += round((5 * $g_332_err) / 16);
        $b_332_earr[$x+1] += round((5 * $b_332_err) / 16);

        $r_332_earr[$x+2] += round($r_332_err / 16);
        $g_332_earr[$x+2] += round($g_332_err / 16);
        $b_332_earr[$x+2] += round($b_332_err / 16);
      }
      else{
        $r_332 = classifyPixel($r, 3);
        $g_332 = classifyPixel($g, 3);
        $b_332 = classifyPixel($b, 2);

        if($r_332 < 0) $r_332 = 0;   if($r_332 > 0xE0) $r_332 = 0xE0;
        if($g_332 < 0) $g_332 = 0;   if($g_332 > 0xE0) $g_332 = 0xE0;
        if($b_332 < 0) $b_332 = 0;   if($b_332 > 0xC0) $b_332 = 0xC0;
      }

      $c8 = ($r_332) | (($g_332) >> 3) | ($b_332 >> 6);	//RGB332
      $c_src8 .= "0x" . str_pad(dechex($c8), 2, '0', STR_PAD_LEFT). ", ";
      $c_src8 .= $a_str;

        /* Conversion for RGB565 */

      if(!strcmp($dith, "enabled")){
        $r_565 = $r + $r_565_nerr + $r_565_earr[$x+1];
        $r_565_earr[$x+1] = 0;
        $g_565 = $g + $g_565_nerr + $g_565_earr[$x+1];
        $g_565_earr[$x+1] = 0;
        $b_565 = $b + $b_565_nerr + $b_565_earr[$x+1];
        $b_565_earr[$x+1] = 0;

        $r_565 = classifyPixel($r_565, 5);
        $g_565 = classifyPixel($g_565, 6);
        $b_565 = classifyPixel($b_565, 5);

        if($r_565 < 0) $r_565 = 0;   if($r_565 > 0xF8) $r_565 = 0xF8;
        if($g_565 < 0) $g_565 = 0;   if($g_565 > 0xFC) $g_565 = 0xFC;
        if($b_565 < 0) $b_565 = 0;   if($b_565 > 0xF8) $b_565 = 0xF8;

        $r_565_err = $r - $r_565;
        $g_565_err = $g - $g_565;
        $b_565_err = $b - $b_565;

        $r_565_nerr = round((7 * $r_565_err) / 16);
        $g_565_nerr = round((7 * $g_565_err) / 16);
        $b_565_nerr = round((7 * $b_565_err) / 16);

        $r_565_earr[$x] += round((3 * $r_565_err) / 16);
        $g_565_earr[$x] += round((3 * $g_565_err) / 16);
        $b_565_earr[$x] += round((3 * $b_565_err) / 16);

        $r_565_earr[$x+1] += round((5 * $r_565_err) / 16);
        $g_565_earr[$x+1] += round((5 * $g_565_err) / 16);
        $b_565_earr[$x+1] += round((5 * $b_565_err) / 16);

        $r_565_earr[$x+2] += round($r_565_err / 16);
        $g_565_earr[$x+2] += round($g_565_err / 16);
        $b_565_earr[$x+2] += round($b_565_err / 16);
      }
      else{
        $r_565 = classifyPixel($r, 5);
        $g_565 = classifyPixel($g, 6);
        $b_565 = classifyPixel($b, 5);

        if($r_565 < 0) $r_565 = 0;   if($r_565 > 0xF8) $r_565 = 0xF8;
        if($g_565 < 0) $g_565 = 0;   if($g_565 > 0xFC) $g_565 = 0xFC;
        if($b_565 < 0) $b_565 = 0;   if($b_565 > 0xF8) $b_565 = 0xF8;
      }

      $c16 = (($r_565) << 8) | (($g_565) << 3) | (($b_565) >> 3);	//RGR565
      $c_src16 .= "0x" . str_pad(dechex(($c16 & 0x00FF)), 2, '0', STR_PAD_LEFT). ", ";
      $c_src16 .= "0x" . str_pad(dechex(($c16 & 0xFF00) >> 8), 2, '0', STR_PAD_LEFT). ", ";
      $c_src16 .= $a_str;

        /* Conversion for RGB888 */

      if(!strcmp($dith, "enabled")){
        $r_888 = $r + $r_888_nerr + $r_888_earr[$x+1];
        $r_888_earr[$x+1] = 0;
        $g_888 = $g + $g_888_nerr + $g_888_earr[$x+1];
        $g_888_earr[$x+1] = 0;
        $b_888 = $b + $b_888_nerr + $b_888_earr[$x+1];
        $b_888_earr[$x+1] = 0;

        $r_888 = classifyPixel($r_888, 8);
        $g_888 = classifyPixel($g_888, 8);
        $b_888 = classifyPixel($b_888, 8);

        if($r_888 < 0) $r_888 = 0;   if($r_888 > 0xFF) $r_888 = 0xFF;
        if($g_888 < 0) $g_888 = 0;   if($g_888 > 0xFF) $g_888 = 0xFF;
        if($b_888 < 0) $b_888 = 0;   if($b_888 > 0xFF) $b_888 = 0xFF;

        $r_888_err = $r - $r_888;
        $g_888_err = $g - $g_888;
        $b_888_err = $b - $b_888;

        $r_888_nerr = round((7 * $r_888_err) / 16);
        $g_888_nerr = round((7 * $g_888_err) / 16);
        $b_888_nerr = round((7 * $b_888_err) / 16);

        $r_888_earr[$x] += round((3 * $r_888_err) / 16);
        $g_888_earr[$x] += round((3 * $g_888_err) / 16);
        $b_888_earr[$x] += round((3 * $b_888_err) / 16);

        $r_888_earr[$x+1] += round((5 * $r_888_err) / 16);
        $g_888_earr[$x+1] += round((5 * $g_888_err) / 16);
        $b_888_earr[$x+1] += round((5 * $b_888_err) / 16);

        $r_888_earr[$x+2] += round($r_888_err / 16);
        $g_888_earr[$x+2] += round($g_888_err / 16);
        $b_888_earr[$x+2] += round($b_888_err / 16);
      }
      else{
        $r_888 = classifyPixel($r, 8);
        $g_888 = classifyPixel($g, 8);
        $b_888 = classifyPixel($b, 8);

        if($r_888 < 0) $r_888 = 0;   if($r_888 > 0xFF) $r_888 = 0xFF;
        if($g_888 < 0) $g_888 = 0;   if($g_888 > 0xFF) $g_888 = 0xFF;
        if($b_888 < 0) $b_888 = 0;   if($b_888 > 0xFF) $b_888 = 0xFF;
      }

      $c24 = ($r_888 << 16) | ($g_888 << 8) | ($b_888);	//RGR888
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

  download($output_name, $c_src);
}

function conv_bin_rgb(){
  global $w;
  global $h;
  global $output_name;
  global $img;
  global $transp;
  global $format;
  global $dith;

  $r_332_earr = array();  // Classification error for next row of pixels
  $g_332_earr = array();
  $b_332_earr = array();
  $r_565_earr = array();
  $g_565_earr = array();
  $b_565_earr = array();
  $r_888_earr = array();
  $g_888_earr = array();
  $b_888_earr = array();

  $r_332_nerr = 0;  // Classification error for next pixel
  $g_332_nerr = 0;
  $b_332_nerr = 0;
  $r_565_nerr = 0;
  $g_565_nerr = 0;
  $b_565_nerr = 0;
  $r_888_nerr = 0;
  $g_888_nerr = 0;
  $b_888_nerr = 0;

  if(!strcmp($dith, "enabled")){
    for($i = 0; $i < $w + 2; ++$i){
      $r_332_earr[$i] = 0;
      $g_332_earr[$i] = 0;
      $b_332_earr[$i] = 0;
      $r_565_earr[$i] = 0;
      $g_565_earr[$i] = 0;
      $b_565_earr[$i] = 0;
      $r_888_earr[$i] = 0;
      $g_888_earr[$i] = 0;
      $b_888_earr[$i] = 0;
    }
  }

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

  for($y = 0; $y < $h; $y++){

    if(!strcmp($dith, "enabled")){
      $r_332_nerr = 0;
      $g_332_nerr = 0;
      $b_332_nerr = 0;
      $r_565_nerr = 0;
      $g_565_nerr = 0;
      $b_565_nerr = 0;
      $r_888_nerr = 0;
      $g_888_nerr = 0;
      $b_888_nerr = 0;
    }

    for($x = 0; $x < $w; $x++){
      $c = imagecolorat($img, $x, $y);

      $r = ($c & 0x00ff0000) >> 16;
      $g = ($c & 0x0000ff00) >> 8;
      $b = ($c & 0x000000ff) >> 0;
      if($transp == "alpha"){
        $a = ($c & 0xff000000) >> 23;       /*Alpha is 7 bit*/
        if($a & 0x02) $a |= 0x01;           /*Repeate the last bit: 0000000 -> 00000000; 1111110 -> 11111111*/
        $a = 255 - $a;
      }

      if($format == "bin_rgb332"){

          /* Conversion for RGB332 */

        if(!strcmp($dith, "enabled")){
          $r_332 = $r + $r_332_nerr + $r_332_earr[$x+1];
          $r_332_earr[$x+1] = 0;
          $g_332 = $g + $g_332_nerr + $g_332_earr[$x+1];
          $g_332_earr[$x+1] = 0;
          $b_332 = $b + $b_332_nerr + $b_332_earr[$x+1];
          $b_332_earr[$x+1] = 0;

          $r_332 = classifyPixel($r_332, 3);
          $g_332 = classifyPixel($g_332, 3);
          $b_332 = classifyPixel($b_332, 2);

          if($r_332 < 0) $r_332 = 0;   if($r_332 > 0xE0) $r_332 = 0xE0;
          if($g_332 < 0) $g_332 = 0;   if($g_332 > 0xE0) $g_332 = 0xE0;
          if($b_332 < 0) $b_332 = 0;   if($b_332 > 0xC0) $b_332 = 0xC0;

          $r_332_err = $r - $r_332;
          $g_332_err = $g - $g_332;
          $b_332_err = $b - $b_332;

          $r_332_nerr = round((7 * $r_332_err) / 16);
          $g_332_nerr = round((7 * $g_332_err) / 16);
          $b_332_nerr = round((7 * $b_332_err) / 16);

          $r_332_earr[$x] += round((3 * $r_332_err) / 16);
          $g_332_earr[$x] += round((3 * $g_332_err) / 16);
          $b_332_earr[$x] += round((3 * $b_332_err) / 16);

          $r_332_earr[$x+1] += round((5 * $r_332_err) / 16);
          $g_332_earr[$x+1] += round((5 * $g_332_err) / 16);
          $b_332_earr[$x+1] += round((5 * $b_332_err) / 16);

          $r_332_earr[$x+2] += round($r_332_err / 16);
          $g_332_earr[$x+2] += round($g_332_err / 16);
          $b_332_earr[$x+2] += round($b_332_err / 16);
        }
        else{
          $r_332 = classifyPixel($r, 3);
          $g_332 = classifyPixel($g, 3);
          $b_332 = classifyPixel($b, 2);

          if($r_332 < 0) $r_332 = 0;   if($r_332 > 0xE0) $r_332 = 0xE0;
          if($g_332 < 0) $g_332 = 0;   if($g_332 > 0xE0) $g_332 = 0xE0;
          if($b_332 < 0) $b_332 = 0;   if($b_332 > 0xC0) $b_332 = 0xC0;
        }
        $c8 = ($r_332) | ($g_332 >> 3) | ($b_332 >> 6);	//RGB332
        $bin .= pack("C", $c8);
        if($transp == "alpha") $bin .= pack("C", $a);

      }
      else if($format == "bin_rgb565"){

          /* Conversion for RGB565 */

        if(!strcmp($dith, "enabled")){
          $r_565 = $r + $r_565_nerr + $r_565_earr[$x+1];
          $r_565_earr[$x+1] = 0;
          $g_565 = $g + $g_565_nerr + $g_565_earr[$x+1];
          $g_565_earr[$x+1] = 0;
          $b_565 = $b + $b_565_nerr + $b_565_earr[$x+1];
          $b_565_earr[$x+1] = 0;

          $r_565 = classifyPixel($r_565, 5);
          $g_565 = classifyPixel($g_565, 6);
          $b_565 = classifyPixel($b_565, 5);

          if($r_565 < 0) $r_565 = 0;   if($r_565 > 0xF8) $r_565 = 0xF8;
          if($g_565 < 0) $g_565 = 0;   if($g_565 > 0xFC) $g_565 = 0xFC;
          if($b_565 < 0) $b_565 = 0;   if($b_565 > 0xF8) $b_565 = 0xF8;

          $r_565_err = $r - $r_565;
          $g_565_err = $g - $g_565;
          $b_565_err = $b - $b_565;

          $r_565_nerr = round((7 * $r_565_err) / 16);
          $g_565_nerr = round((7 * $g_565_err) / 16);
          $b_565_nerr = round((7 * $b_565_err) / 16);

          $r_565_earr[$x] += round((3 * $r_565_err) / 16);
          $g_565_earr[$x] += round((3 * $g_565_err) / 16);
          $b_565_earr[$x] += round((3 * $b_565_err) / 16);

          $r_565_earr[$x+1] += round((5 * $r_565_err) / 16);
          $g_565_earr[$x+1] += round((5 * $g_565_err) / 16);
          $b_565_earr[$x+1] += round((5 * $b_565_err) / 16);

          $r_565_earr[$x+2] += round($r_565_err / 16);
          $g_565_earr[$x+2] += round($g_565_err / 16);
          $b_565_earr[$x+2] += round($b_565_err / 16);
        }
        else{
          $r_565 = classifyPixel($r, 5);
          $g_565 = classifyPixel($g, 6);
          $b_565 = classifyPixel($b, 5);

          if($r_565 < 0) $r_565 = 0;   if($r_565 > 0xF8) $r_565 = 0xF8;
          if($g_565 < 0) $g_565 = 0;   if($g_565 > 0xFC) $g_565 = 0xFC;
          if($b_565 < 0) $b_565 = 0;   if($b_565 > 0xF8) $b_565 = 0xF8;
        }

	        $c16 = ($r_565 << 8) | ($g_565 << 3) | ($b_565 >> 3);	//RGR565
	        $bin .= pack("v", $c16);
	        if($transp == "alpha") $bin .= pack("C", $a);

      }
      else if($format == "bin_rgb888"){

            /* Conversion for RGB888 */

        if(!strcmp($dith, "enabled")){
          $r_888 = $r + $r_888_nerr + $r_888_earr[$x+1];
          $r_888_earr[$x+1] = 0;
          $g_888 = $g + $g_888_nerr + $g_888_earr[$x+1];
          $g_888_earr[$x+1] = 0;
          $b_888 = $b + $b_888_nerr + $b_888_earr[$x+1];
          $b_888_earr[$x+1] = 0;

          $r_888 = classifyPixel($r_888, 8);
          $g_888 = classifyPixel($g_888, 8);
          $b_888 = classifyPixel($b_888, 8);

          if($r_888 < 0) $r_888 = 0;   if($r_888 > 0xFF) $r_888 = 0xFF;
          if($g_888 < 0) $g_888 = 0;   if($g_888 > 0xFF) $g_888 = 0xFF;
          if($b_888 < 0) $b_888 = 0;   if($b_888 > 0xFF) $b_888 = 0xFF;

          $r_888_err = $r - $r_888;
          $g_888_err = $g - $g_888;
          $b_888_err = $b - $b_888;

          $r_888_nerr = round((7 * $r_888_err) / 16);
          $g_888_nerr = round((7 * $g_888_err) / 16);
          $b_888_nerr = round((7 * $b_888_err) / 16);

          $r_888_earr[$x] += round((3 * $r_888_err) / 16);
          $g_888_earr[$x] += round((3 * $g_888_err) / 16);
          $b_888_earr[$x] += round((3 * $b_888_err) / 16);

          $r_888_earr[$x+1] += round((5 * $r_888_err) / 16);
          $g_888_earr[$x+1] += round((5 * $g_888_err) / 16);
          $b_888_earr[$x+1] += round((5 * $b_888_err) / 16);

          $r_888_earr[$x+2] += round($r_888_err / 16);
          $g_888_earr[$x+2] += round($g_888_err / 16);
          $b_888_earr[$x+2] += round($b_888_err / 16);
        }
        else{
          $r_888 = classifyPixel($r, 8);
          $g_888 = classifyPixel($g, 8);
          $b_888 = classifyPixel($b, 8);

          if($r_888 < 0) $r_888 = 0;   if($r_888 > 0xFF) $r_888 = 0xFF;
          if($g_888 < 0) $g_888 = 0;   if($g_888 > 0xFF) $g_888 = 0xFF;
          if($b_888 < 0) $b_888 = 0;   if($b_888 > 0xFF) $b_888 = 0xFF;
        }

        $c24 = ($a << 24) | ($r_888 << 16) | ($g_888 << 8) | ($b_888);	//RGR888
        $bin .= pack("V", $c24);
      }
    }
  }

  $output_name .= ".bin";
  download($output_name, $bin);
}

function download($name, $content){
  global $offline;

  $file_name = $name.'.c';

  if($offline){
    $file = fopen($file_name, "w");
    fwrite($file, $content);
    fclose($file);
  }
  else{
    header('Content-Type: application/text');
    header('Content-disposition: attachment; filename='.$file_name);
    header('Content-Length: ' . strlen($content));
    echo($content);
  }
}

function classifyPixel($value, $bits){
  $tmp = 1 << (8 - $bits);
  return round($value / $tmp, 0, PHP_ROUND_HALF_DOWN) * $tmp;
}
?>
