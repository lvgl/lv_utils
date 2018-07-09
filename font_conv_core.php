<?php

$offline = 0;
if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_POST);
  $offline = 1;
}

putenv('GDFONTPATH=' . realpath('.'));
    
if($offline == 0) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $font_file = $_FILES["font_file"]["tmp_name"];
    $font_name = $_FILES["font_file"]["name"];
    $output_name = $_POST["name"];
    $h_px = $_POST["height"];
    $bpp = $_POST['bpp'];  
    $unicode_start = $_POST["uni_first"];
    $unicode_last = $_POST["uni_last"];
    $utf8_list = $_POST["list"];
    $builtin = $_POST["built_in"];
    
    if(!empty($_POST["monospace"])) $monospace = $_POST["monospace"];
    else $monospace = 0;
    
    if(!empty($_POST["scale"])) $scale = $_POST["scale"];
    else $scale = 100;
    
    if(!empty($_POST["base_shift"])) $base_shift = $_POST["base_shift"];
    else $base_shift = 0;
    
    
} else {
    if(isset($_POST["name"])) {
        $output_name = $_POST["name"];
    } else {
        echo("Mising Name\n");
        exit(0);
    }
 
    if(isset($_POST["height"])) {
        $h_px = $_POST["height"];
    } else {
        echo("Mising Height\n");
        exit(0);
    }
    
    if(isset($_POST["bpp"])) {
        $bpp = $_POST["bpp"];
    } else {
        echo("Mising Bpp\n");
        exit(0);
    }
    
    if(isset($_POST["font"])) {
        $font_file = $_POST["font"];
        $font_name = $_POST["font"];
    } else {
        echo("Mising Font\n");
        exit(0);
    }
    
    if(isset($_POST["uni_first"])) {
    $unicode_start = $_POST["uni_first"];
    } else {
        echo("Mising First unicode\n");
        exit(0);
    }
    
    if(isset($_POST["uni_last"])) {
     $unicode_last = $_POST["uni_last"];
    } else {
        echo("Mising Last unicode\n");
        exit(0);
    }
    
    
    if(isset($_POST["list"])) {
        $utf8_list = $_POST["list"];
    } else {
        $utf8_list = "";
    }
    
    
    if(isset($_POST["built_in"])) {
        $builtin = $_POST["built_in"];
    } else {
        $builtin = 0;
    }
    
    if(isset($_POST["monospace"])) {
        $monospace = $_POST["monospace"];
    } else {
        $monospace = 0;
    }
    
    if(isset($_POST["scale"])) {
        $scale = $_POST["scale"];
    } else {
        $scale = 100;
    }
    
    if(isset($_POST["base_shift"])) {
        $base_shift = $_POST["base_shift"];
    } else {
        $base_shift = 0;
    }
}

$h_pt = $h_px  * 3;
$canvas_w = 5 * $h_px;
$c_src = "";
$c_glyph_bitmap = "";
$c_glyph_dsc = "";
$c_utf8_list = "";
$c_font_dsc = "";
$c_info = "";
$unicode_start_str = "U+" . str_pad(dechex($unicode_start), 4, '0', STR_PAD_LEFT);
$unicode_last_str = "U+" . str_pad(dechex($unicode_last), 4, '0', STR_PAD_LEFT);;
$unicode_start_letter = utf8($unicode_start);
$unicode_last_letter = utf8($unicode_last);

$byte_cnt = 0;
$base_line_ofs = 0;
height_corr();
$base_line_ofs -= $base_shift;
if($scale <= 0) $scale = 100;
$h_pt = $h_pt * $scale / 100;
$h_pt = floor($h_pt/0.75);   /*Be sure h_pt is dividabe with 0.75*/ 
$h_pt = $h_pt * 0.75;

if($builtin) {
    $c_src = "
#include \"../lv_font.h\"\n";

    $c_src .= "\n#if USE_". strtoupper($output_name) . " != 0\t/*Can be enabled in lv_conf.h*/\n\n";

$c_info = "/***********************************************************************************
 * $font_name $h_px px Font in $unicode_start_str ($unicode_start_letter) .. $unicode_last_str ($unicode_last_letter)  range with all bpp";
    

} else {
$c_src = "
#include \"lvgl/lv_misc/lv_font.h\"\n\n";

$c_info = "/***********************************************************************************
 * $font_name $h_px px Font in $unicode_start_str ($unicode_start_letter) .. $unicode_last_str ($unicode_last_letter)  range with $bpp bpp";

}

$utf8_array = array();
$unicode_array = array();

if(strlen($utf8_list) != 0) {
    $utf8_list = html_entity_decode($utf8_list, ENT_COMPAT | ENT_HTML401, ini_get("default_charset"));
	$list_rep = str_replace("\\", "\\\\", $utf8_list);
	$list_rep = str_replace('$', '\\$', $utf8_list);
	$list_rep = str_replace('\'', '\\\'', $utf8_list);
	$c_info .= "\n * Sparse font with only these characters: ";

    /*Order the list*/
    for($i = 0; $i < mb_strlen($utf8_list); $i++) {
		$letter = mb_substr($utf8_list, $i, 1);
		$unicode_act = ord_utf8( $letter);
        if($unicode_act == 0 || $unicode_act < $unicode_start || $unicode_act > $unicode_last) continue;
		$unicode_array[$i] = $unicode_act;
    }
    
    sort($unicode_array, SORT_NUMERIC);
    
    for ($i = 0; $i < count($unicode_array); $i++) {
        $utf8_array[$i] = utf8($unicode_array[$i]);
        $c_info .= $utf8_array[$i];
    }
    

	$c_utf8_list  = "/*List of unicode characters*/
static const uint32_t $output_name". "_unicode_list[] = {";


	for($i = 0; $i < count($unicode_array); $i++) {
		$unicode_str = str_pad(dechex($unicode_array[$i]), 4, '0', STR_PAD_LEFT);
		$c_utf8_list .= "\n  $unicode_array[$i],\t/*Unicode: U+$unicode_str ($utf8_array[$i])*/"; 
	}

	$c_utf8_list .= "\n  0,    /*End indicator*/\n};";
}
 
$c_info .= "\n***********************************************************************************/\n";

$c_src .= $c_info; 

$c_glyph_bitmap = "
/*Store the image of the letters (glyph)*/
static const uint8_t $output_name"."_glyph_bitmap[] = 
{\n";

$c_glyph_dsc = "
/*Store the glyph descriptions*/
static const lv_font_glyph_dsc_t $output_name" . "_glyph_dsc[] = 
{\n";

$c_font_dsc = "lv_font_t $output_name = 
{    
    .unicode_first = $unicode_start".",\t/*First Unicode letter in this font*/
    .unicode_last = $unicode_last".",\t/*Last Unicode letter in this font*/
    .h_px = $h_px".",\t\t\t\t/*Font height in pixels*/
    .glyph_bitmap = $output_name"."_glyph_bitmap,\t/*Bitmap of glyphs*/
    .glyph_dsc = $output_name"."_glyph_dsc,\t\t/*Description of glyphs*/";
if(count($utf8_array)) { $c_font_dsc .= "
    .unicode_list = $output_name"."_unicode_list,\t/*List of unicode characters*/
    .get_bitmap = lv_font_get_bitmap_sparse,\t/*Function pointer to get glyph's bitmap*/
    .get_width = lv_font_get_width_sparse,\t/*Function pointer to get glyph's width*/\n";
} else { $c_font_dsc .= "
    .unicode_list = NULL,\t/*Every character in the font from 'unicode_first' to 'unicode_last'*/
    .get_bitmap = lv_font_get_bitmap_continuous,\t/*Function pointer to get glyph's bitmap*/
    .get_width = lv_font_get_width_continuous,\t/*Function pointer to get glyph's width*/\n";
}

if($builtin) {
$c_font_dsc .= "#if USE_" . strtoupper($output_name) . " == 1
    .bpp = 1,\t\t\t\t/*Bit per pixel*/
 #elif USE_" . strtoupper($output_name) . " == 2
    .bpp = 2,\t\t\t\t/*Bit per pixel*/
 #elif USE_" . strtoupper($output_name) . " == 4
    .bpp = 4,\t\t\t\t/*Bit per pixel*/
 #elif USE_" . strtoupper($output_name) . " == 8
    .bpp = 8,\t\t\t\t/*Bit per pixel*/
#endif\n";
} else {
    $c_font_dsc .= "    .bpp = $bpp,\t\t\t\t/*Bit per pixel*/\n";
}
if($monospace) {
    $c_font_dsc .= "    .monospace = $monospace,\t\t/*Fix width (0: if not used)*/\n";
    }
    $c_font_dsc .= "    .next_page = NULL,\t\t/*Pointer to a font extension*/
};";


// Create the image
$im = imagecreatetruecolor($canvas_w, $h_px);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);

if(!$builtin) {
    convert_all_letters();
} else {
    $c_glyph_bitmap .= "#if USE_" . strtoupper($output_name) . " == 1\n";
    $c_glyph_dsc .= "#if USE_" . strtoupper($output_name) . " == 1\n";
    $bpp = 1;
    $byte_cnt = 0;
    convert_all_letters();
    
    $c_glyph_bitmap .= "\n#elif USE_" . strtoupper($output_name) . " == 2\n";
    $c_glyph_dsc .= "\n#elif USE_" . strtoupper($output_name) . " == 2\n";
    $bpp = 2;
    $byte_cnt = 0;
    convert_all_letters();
    
    $c_glyph_bitmap .= "\n#elif USE_" . strtoupper($output_name) . " == 4\n";
    $c_glyph_dsc .= "\n#elif USE_" . strtoupper($output_name) . " == 4\n";
    $bpp = 4;
    $byte_cnt = 0;
    convert_all_letters();
    
    $c_glyph_bitmap .= "\n#elif USE_" . strtoupper($output_name) . " == 8\n";
    $c_glyph_dsc .= "\n#elif USE_" . strtoupper($output_name) . " == 8\n";
    $bpp = 8;
    $byte_cnt = 0;
    convert_all_letters();
    
    $c_glyph_bitmap .= "\n#endif\n";
    $c_glyph_dsc .= "\n#endif\n";
    

} 

$c_glyph_bitmap .= "};";
$c_glyph_dsc .= "};";
$c_src .= $c_glyph_bitmap . "\n\n" . $c_glyph_dsc . "\n\n";
if(strlen($c_utf8_list)) $c_src .= $c_utf8_list . "\n\n";
$c_src .= $c_font_dsc;

if($builtin) {
    $c_src .= "\n\n#endif /*USE_". strtoupper($output_name) . "*/\n";
}

imagedestroy($im);

download($output_name, $c_src);


function convert_all_letters()
{
    global $utf8_array;
    global $unicode_array;
    global $unicode_start;
    global $unicode_last;
    global $font_file;
    global $canvas_w;
    global $h_px;
    global $h_pt;
    global $bpp;
    global $base_line_ofs;
    global $im;
    global $black;
    global $white;

    if(count($utf8_array) == 0) {
        for($unicode_act = $unicode_start; $unicode_act <= $unicode_last; $unicode_act++) {
	        imagefilledrectangle($im, 0, 0, $canvas_w, $h_px, $black);
            $letter = utf8($unicode_act);
            $co = imagettftext($im, $h_pt, 0, 0, $h_pt + $base_line_ofs, $white, $font_file, $letter);    //Size and Y in pt NOT px
            $w_px = $co[1] - $co[0];
            convert_letter($im, $unicode_act,  $w_px, $bpp);
        }
    } else {
        for($i = 0; $i < count($unicode_array); $i++) {
            imagefilledrectangle($im, 0, 0, $canvas_w, $h_px, $black);
            $co = imagettftext($im, $h_pt, 0, 0, $h_pt + $base_line_ofs, $white, $font_file, $utf8_array[$i]);    //Size and Y in pt NOT px
            
            $w_px = $co[1] - $co[0];
            convert_letter($im, $unicode_array[$i],  $w_px, $bpp);
        }
    }
}

function height_corr()
{
    global $h_pt;
    global $h_px;
    global $font_file;
    global $base_line_ofs;
    global $unicode_start;
    global $unicode_last;
   
    $im = imagecreatetruecolor($h_pt * 1, $h_pt * 1);  /*The size is not important*/
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $max_h = $h_px;
    $test_text = '| [§@#ß$ÄÁŰ¿?`\'"_pyj';

    if($unicode_start >= 61440 && $unicode_last <= 62190) {
        $test_text .= " ";
        //echo("symbol<br>");
    }

    while($h_pt > 0) {
        $co = imagettftext($im, $h_pt, 0, 0, $h_pt, $white, $font_file, $test_text );
        
        $h = $co[1] - $co[7];
       // echo("h: $h <br>");
        if($h > $max_h) $h_pt-=0.75;
        else {
            $base_line_ofs = - $co[7];
            break;
        }
    }
    
    imagedestroy($im);
}

function utf8($num)
{
    if($num<=0x7F)       return chr($num);
    if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
    if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
    if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
    return '';
}

function ord_utf8($s){
    $len = strlen($s);
    if($len == 0) return 0;
    if($len == 1) $s = unpack('C*',$s[0]."0"."0"."0");
    else if($len == 2) $s = unpack('C*',$s[0].$s[1]."0"."0");
    else if($len == 3) $s = unpack('C*',$s[0].$s[1].$s[2]."0");
    else if($len == 4) $s = unpack('C*',$s[0].$s[1].$s[2].$s[3]);
    
    return (int)$s[1]<(1<<7)?$s[1]:
    ($s[1]>239&&$s[2]>127&&$s[3]>127&&$s[4]>127?(7&$s[1])<<18|(63&$s[2])<<12|(63&$s[3])<<6|63&$s[4]:
    ($s[1]>223&&$s[2]>127&&$s[3]>127?(15&$s[1])<<12|(63&$s[2])<<6|63&$s[3]:
    ($s[1]>193&&$s[2]>127?(31&$s[1])<<6|63&$s[2]:0)));
}

function convert_letter($glyph, $unicode,  $w, $bpp) {
   global $h_px;
   global $c_glyph_bitmap;
   global $c_glyph_dsc;
   global $byte_cnt;
   $w = $h_px * 5 - 1; //assume bigger width to be sure
   $whitespace = 0;	

   //Decrement width to trim the the empty columns
   for($x = $w; $x >= 0; $x--) {
        $c = 0;
        for($y = 0; $y < $h_px; $y++) {
            $c = imagecolorat($glyph, $x, $y);
            $c = $c & 0xFF;
            if($c != 0x00) break;
        }
        
        if($c != 0x00) break;
        $w--;
   }
	
	if($x < 0) {
		$whitespace = 1;
		$w = floor($h_px / 4);      /*Width of white space is 1/3 height*/
	}
	
   //Trim leading empty columns
   $first_col = 0;
   if(!$whitespace) {
	   for($first_col = 0; $first_col <= $w; $first_col++) {
	        $c = 0;
	        for($y = 0; $y < $h_px; $y++) {
	            $c = imagecolorat($glyph, $first_col, $y);
	            $c = $c & 0xFF;
	            if($c != 0x00) break;
	        }
	        
	        if($c != 0x00) break;
	   }
	   $w -= $first_col;
	}

   
   $w++; //E.g. x1=5, x2=8 -> w=3+1
   $letter = utf8($unicode);
   $unicode_str = str_pad(dechex($unicode), 4, '0', STR_PAD_LEFT);
   $c_glyph_bitmap .= "  /*Unicode: U+$unicode_str ($letter) , Width: $w */\n";

   $comment = "";
   $data = "";
   $act_byte;
    for($y = 0; $y < $h_px; $y++) {
        $act_byte = 0;
        $comment = "//";
        $data = "  ";
        
        for($x = 0; $x < $w; $x++) {
            $act_byte = $act_byte << (1 * $bpp);
            $x_act = $x + $first_col - $x_mono_ofs;
            if($x_act >= 0) {
                $c = imagecolorat($glyph, $x_act , $y);
            } else {
                $c = 0x00;
            }
            $c = $c & 0xFF;
            $act_byte |= $c >> (8 - $bpp);
    
           $c = ($c >> (8 - $bpp)) << (8 - $bpp);       /*Round based on bpp*/
            
            if($c >= 192) {
                $comment .= "@";
            } else if($c >= 128) {
                $comment .= "%";
            } else if($c >= 64) {
                $comment .= "+";
            } else {
                $comment .= ".";
            }

            
            if(((($x + 1) * $bpp) % 8) == 0 && ($x != 0 || $bpp == 8)) {
                $hex = str_pad(dechex($act_byte), 2, '0', STR_PAD_LEFT);
                $data .= "0x" . $hex . ", ";     
                $act_byte = 0;
            } 
        }
    
        /*Flush the remaining part*/    
        if(($x * $bpp) % 8 != 0) {
            /*Shift bit to the left most position*/
            $act_byte  = $act_byte << (((8 - ($x * $bpp) % 8)));
            $hex = str_pad(dechex($act_byte), 2, '0', STR_PAD_LEFT);
            $data .= "0x" . $hex . ", ";     
            $act_byte = 0;
            
        }
        
        $c_glyph_bitmap .= "$data $comment \n";
    }
    
    $size = $h_px * floor($w * $bpp / 8);
    if(($w * $bpp) % 8) $size += $h_px;     //Extra byte to store the reminder
    
    $c_glyph_dsc .= "  {.w_px = $w,\t.glyph_index = $byte_cnt},\t/*Unicode: U+$unicode_str ($letter)*/\n";
    $c_glyph_bitmap .= "\n\n";
    $byte_cnt += $size;
}


function download($name, $c_data)
{
    global $offline;
    
    $file_name = $name.'.c';


    if($offline) {
        $file = fopen($file_name, "w");
        fwrite($file, $c_data);
        fclose($file);
    } else {
        header('Content-Type: application/text');
        header('Content-disposition: attachment; filename='.$file_name);
        header('Content-Length: ' . strlen($c_data));
        echo($c_data);
    }
}

?>

