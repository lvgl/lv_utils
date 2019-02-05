# Offline version of Online convert tools

These PHP scrips are the online converter tools:

* Image converter: https://littlevgl.com/image-to-c-array
* Font converter: https://littlevgl.com/ttf-font-to-c-array

Here you you can see how they work or download them to run offline.

## Get started

This guide shows how to use the converters offline in a command line on **Linux**

1. Install PHP: `sudo apt-get install php7.2-cli`
2. Intall the Image manipulator extension for PHP: `sudo apt-get install php7.2-gd`
3. Clone or download the lv_utils repository: `git clone https://github.com/littlevgl/lv_utils.git`
4. Go to the *lv_utils* directory.

## Run the Font converter offline

5. Copy a TTF file you want to use into the *lv_utils* directory
6. Run the script: `php font_conv_core.php "name=arial_20&font=arial.ttf&height=20&bpp=4&uni_first=32&uni_last=126"`
7. Chek the craeted C file in *lv_utils* directory.

The required arguments of the script:

- **name** name of the output file and font
- **font** name of font file to use (must be a *.ttf file)
- **height** desired height in px
- **bpp** Bit-per-pixel (1, 2, 4 or 8)
- **uni_first** the first unicode character to convert
- **uni_last** the ast unicode character to convert

Optional arguments:

- **built_in** convert a built in font with all bpp (0 or 1)
- **list** list of characters to include (must be in rango of *uni_first* and *uni_last* and in ascendant order). E.g. "list=123abc"
- **scale** scale up/down the letters [%] E.g. "scale=120"
- **base_shift** shift the base line [px] E.g. "base_shift=3"
- **monospace** use this fixed width [px] E.g. "monospace=12"
 
### Example with all options

Convert only the numbers and + - sign with Arial font (The plus sign is replaced with %2B):
`php font_conv_core.php "name=arial_num_20&font=arial.ttf&height=20&bpp=8&uni_first=32&uni_last=126&list=%2B-0123456789&built_in=1"`
 
### Using the generated font in LittlevGL

 * Copy the result C file into your LittlevGL project  
 * In a C file of your application declare the font as: `extern lv_font_t my_font_name;` or simply `LV_FONT_DECLARE(my_font_name);`
  * Set the font in a style: `style.text.font = &my_font_name;`


## Run the Image converter offline

5. Copy a BMP, JPG or PNG file you want to use into the *lv_utils* directory
6. Run the script: `php img_conv_core.php "name=wallpaper&img=red_flower.png"`
7. Chek the created C file in *lv_utils* directory.

The required arguments of the script:

- **name** name of the output file and image
- **img** an image file

Optional arguments:

- **cf** color format. Possible values are: `true_color`, `true_color_alpha`, `true_color_chroma`, `indexed_1`, `indexed_2`, `indexed_4`, `indexed_8`, `alpha_1`, `alpha_2`, `alpha_4`, `alpha_8`, `raw`, `raw_alpha`, `raw_chroma`. The default is: `true_color`.
- **format** C array or Binary output. Possible values are: `c_array`, `bin_332`, `bin_565`, `bin_565_swap`, `bin_888`. Default is: `c_array`.

### Example with all options

Convert a *bunny.png* with alpha for all pixels to C array:
`php img_conv_core.php "name=icon&img=bunny.png&format=c_array&cf=true_color_alpha"`

### Using the generated image in LittlevGL

* For C arrays
   - Copy the result C file into your LittlevGL project
   - In a C file of your application declare the image as: `extern const lv_img_t my_image_name;` or `LV_IMG_DECLARE(my_image_name);`
   - Set the image for an lv_img object: `lv_img_set_src(img1, &my_image_name);`
* For externally binary files (e.g. SD card)
   - Set up a new driver. To learn more read the [Tutorial](https://github.com/littlevgl/lv_examples/blob/master/lv_tutorial/6_images/lv_tutorial_images.c).
   - Set the image for an lv_img object: `lv_img_set_src(img1, "S:/path/to/image");`
   
# Other offline tools
   
## BDF Font Converter

`utils/bdf_font_converter.py` converts [BDF](https://en.wikipedia.org/wiki/Glyph_Bitmap_Distribution_Format) files into a LittlevGL compatible c file. This converter is useful for generating pixel-perfect fonts, especially for monochrome displays.

 Help can be printed out by:

```sh
python bdf_font_converter.py --help
```

 Typical use case (generates `crox3hb.c`):

```sh
python bdf_font_converter.py win_crox3hb.bdf crox3hb
```
