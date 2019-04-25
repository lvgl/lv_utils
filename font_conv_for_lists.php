<?php
  echo "Character Convert: Unicode, Decimal NCRs, Url Encoded\n";

  /*
   * Configs
   */
  // https://fontawesome.com/v4.7.0/icons/
  // label: unicode
  $unicodes = array(
    "THERMOMETER_EMPTY" => "f2cb",
    "TINT             " => "f043",
    "BALANCE_SCALE    " => "f24e",
    "BARS             " => "f0c9",
    "MICROCHIP        " => "f2db"
  );

  $height = 100;
  $name = "font_symbol_extra_" . $height;
  $font = "FontAwesome.ttf";
  $uni_first = 61440;
  $uni_last = 62190;
  $bpp = 8;

  $output_dir = "./output/";
  $name_with_c = "$name.c";

  // a. Parameters
  $decimal_ncrs = "";

  foreach ($unicodes as $label => $unicode) {
    $utf8 = unicode_to_utf8($unicode);
    $decimal = unicode_to_decimal_ncrs($unicode);
    $encoded = urlencode($decimal);

    $decimal_ncrs = $decimal_ncrs . $decimal;

    echo "\n";
    echo "Unicode: $unicode\n";
    echo "Decimal NCRs: $decimal_ncrs\n";
    echo "Url Encoded: $encoded\n";
    echo "UTF-8: $utf8";
    echo "\n";
  }
  echo "\n";

  $encodeds = urlencode($decimal_ncrs);

  // b. Convert
  $cmd = "php font_conv_core.php 'name=$name&font=$font&height=$height&bpp=$bpp&uni_first=$uni_first&uni_last=$uni_last&built_in_sym=0&list=$encodeds'";
  echo "Running: $cmd\n";

  shell_exec($cmd);

  // c. Move
  shell_exec("mkdir $output_dir");
  shell_exec("mv $name_with_c $output_dir/$name_with_c");

  // d. Usage
  usage($name, $unicodes, $height);

  function usage($name, $unicodes, $height)
  {
    echo "\nUSAGE\n\n";

    // a. define
    $labels = "";
    echo "a. Define\n";
    foreach ($unicodes as $label => $unicode) {
      $utf8 = unicode_to_utf8($unicode);

      $str = <<<HERO
#define LV_SYMBOL_$label \t"$utf8"\n
HERO;

      echo $str;

      $labels = $labels . "LV_SYMBOL_$label ";
    }

    echo "\n";

    // b. add font
    echo "b. Add font\n";
    $lvgl_font = "lv_font_symbol_$height";
    echo "lv_font_add(&$name, &$lvgl_font);\n";

    // c. Style
    $str = <<<HERO
static lv_style_t style;
lv_style_copy(&style, &lv_style_plain);
style.text.font = &$lvgl_font;
HERO;
    echo "$str\n";

    // c. Declare
    echo "\nc. Declare\n";
    echo "LV_FONT_DECLARE($name)\n";

    // d. Use it;)
    echo "\nd. Use it;)\n";
    $str = <<<HERO
lv_obj_t * label = lv_label_create(scr, NULL);
lv_label_set_style(label, &style);
lv_label_set_text(label, $labels);
HERO;
    echo "$str\n";
  }

  // Usage: unicode_to_utf8("f2cb");
  function unicode_to_utf8($unicode)
  {
    $tmp = json_decode('"\u' . $unicode . '"');
    $n = strlen($tmp);
    $str = "";

    for ($i = 0; $i < $n; $i++) {
      $d = ord($tmp[$i]);

      $h = dechex($d);

      $str = $str . "\x" . $h;
    }

    return $str;
  }

  // Usage: unicode_to_decimal_ncrs("f2cb");
  function unicode_to_decimal_ncrs($unicode)
  {
    $dec = "&#" . hexdec($unicode) . ";";

    return $dec;
  }
exit;

/*
Character Convert: Unicode, Decimal NCRs, Url Encoded

Unicode: f2cb
Decimal NCRs: &#62155;
Url Encoded: %26%2362155%3B
UTF-8: \xef\x8b\x8b

Unicode: f043
Decimal NCRs: &#62155;&#61507;
Url Encoded: %26%2361507%3B
UTF-8: \xef\x81\x83

Running: php font_conv_core.php 'name=font_symbol_extra_30&font=FontAwesome.ttf&height=30&bpp=8&uni_first=61440&uni_last=62190&built_in_sym=0&list=%26%2362155%3B%26%2361507%3B'

USAGE

a. Define
#define LV_SYMBOL_THERMOMETER_EMPTY     "\xef\x8b\x8b"
#define LV_SYMBOL_TINT                  "\xef\x81\x83"

b. Add font
lv_font_add(&font_symbol_extra_30, &lv_font_symbol_30);
static lv_style_t style;
lv_style_copy(&style, &lv_style_plain);
style.text.font = &lv_font_symbol_30;

c. Use it;)
lv_obj_t * label = lv_label_create(scr, NULL);
lv_label_set_style(label, &style);
lv_label_set_text(label, LV_SYMBOL_THERMOMETER_EMPTY LV_SYMBOL_TINT              );
*/

  // $decimal_ncrs = "&#" . hexdec($unicode) . ";";
  // $encoded = urlencode($decimal_ncrs);

  // $unicode = "f2cb f2d9";
  // $decimal_ncrs = "&#" . hexdec("f2cb") . ";&#" . hexdec("f2d9") . ";";
  // $encoded = urlencode($decimal_ncrs);

  // echo "Unicode: $unicode\n";
  // echo "Decimal NCRs: $decimal_ncrs\n";
  // echo "Url Encoded: $encoded\n";

  // $cmd = "php font_conv_core.php 'name=font_extra_30&font=FontAwesome.ttf&height=30&bpp=8&uni_first=61440&uni_last=62190&built_in_sym=0&list=$encoded'";
  // echo "Running: $cmd";

  // shell_exec($cmd);

  // echo "\n";

  /*
  Character Convert: Unicode, Decimal NCRs, Url Encoded
  Unicode: \uf2ba
  Decimal NCRs: &#62138;
  Url Encoded: %26%2362138%3B

  echo "hello world" | php -R 'echo str_replace("world","stackoverflow", $argn);'

  php font_conv_core.php "name=font_extra_30&font=fontawesome.ttf&height=30&bpp=8&uni_first=61440&uni_last=63252&built_in_sym=0&list=%26%2362156%3B"

  php font_conv_core.php "name=font_extra_30&font=fontawesome.ttf&height=30&bpp=8&uni_first=61440&uni_last=62190&built_in_sym=0&list=%26%2362156%3B"

  php font_conv_core.php "name=font_extra_30&font=FontAwesome.ttf&height=30&bpp=8&uni_first=61440&uni_last=63252&built_in_sym=0&list=%26%2363252%3B"

  php font_conv_core.php "name=font_extra_30&font=fa-solid-900.ttf&height=50&bpp=8&uni_first=61440&uni_last=63252&built_in_sym=0&list=%26%2363252%3B"
  
  function ascii_to_dec($str)
  {
    for ($i = 0, $j = strlen($str); $i < $j; $i++) {
      $dec_array[] = ord($str{$i});
    }
    return $dec_array;
  }

  $unicode = "\uf2cb";
  // echo json_decode('"\u00c9"');
  // echo mb_convert_encoding("\x10\x00", 'UTF-8', 'UTF-16BE');
  // echo mb_convert_encoding('&#x1000;', 'UTF-8', 'HTML-ENTITIES');
  // echo("\x48\x65\x6C\x6C\x6F\x20\x57\x6F\x72\x6C\x64\x21");
  // echo json_encode(json_decode($unicode), JSON_UNESCAPED_UNICODE);
  // echo mb_strtolower($unicode, 'UTF-8');
  // var_dump(ascii_to_dec($unicode));
  // $str=json_decode($unicode);
  // $str=json_decode('"\uf2cb"');
  $str=json_decode('"' . $unicode . '"');
  echo var_dump(ord($str[0]));

  echo "\n";
  $unicode = "f2cb";
  $utf8 = unicode_to_utf8($unicode);
  echo "UTF-8: $utf8";
  echo "\n";
  */
?>