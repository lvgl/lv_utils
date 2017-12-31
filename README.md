# Utilities
Utilities to convert 
* pictures
* operation system fonts 

to **raw C arrays**.

The utilities are written to be compatible with the **Littlev Graphics Library**. 

GitHub: https://github.com/littlevgl/lvgl
Website: https://littlevgl.com

## Usage
Both of the utilities are Python scripts. So you has to install Python on your operation system.

**Clone or download** the repository: `git clone https://github.com/littlevgl/lv_utils.git`

### Image converter
It vraetes a `c` and a `bin` file fro mthe image. 
The `c` file contains 8, 16 and 32 bit color depth arrays but 
the binary file containsonly the specified color depth (see below)

The `c` file can be copied and cmpiled with your project.
The binary file an be used on external memory devices (e.g. SD card)

1. Copy an image to img_conv folder
2. Open a Terminal and go to this directory
3. Type to the Terminal: `python img_cony.py -f test.png -c 16`. Parameters:
   * **-f**: Image file name
   * **-c** Color depth: 8/16/24 (only for the binary file)
   * **-t** Chroma keyed: pixels with a specific color (lv_conf.h LV_COLOR_TRANSP) are not drawn
4. Check the output *img_test.c* and *img_test.bin*

For more information visit: https://littlevgl.com/image-converter

### Font converter
fnt2c.py reads bitmap font output from the Bitmap Font Generator by
AngelCode: http://www.angelcode.com/products/bmfont/  and outputs byte table 
arrays in C language which is compatible with LittlevGL fonts.

1. Install **BMfont** with font_create/install_bmfont_1.13.exe
2. Open BMfont:
   1. In **Options/Font settings** choose a font, set its size and other settings (bold, italian etc.)
   2. In **Options/Export settings** choose XML file format and .png texture. Set the texture width and height to 2048.
   3. Save the *fnt* file: **Options/Save bitmap font as…**
3. fnt2.c.py usage: `python fnt2c.py -f <font_name> [-o <output file> -s <start unicode> -e <end unicide>]`
   * **-f, --font**    name of the font file without any extension (e.g. arial_10)
   * **-o, --output**  name of the output file without any extension (e.g. arial_10_cyrillic).   Optional, default: font name
   * **-s, --start**   first unicode charater to convert (e.g. 1024).                            Optional, default: 32
   * **-e, --end**     last unicode charater to convert (e.g. 1279).                             Optional, default: 126
4. Convert the ASCII characters from dejavu_20.fnt/png and save to devaju_20.c/h: 
   * `python fnt2c.py -f dejavu_20`
5. Convert the Cyrillic code page from dejavu_20.fnt/png and save to devaju_20_cyrillic.c/h: 
   * `python fnt2c.py -f dejavu_20 -o dejavu_20_cyrillic -s 1024 -e 1279`

e them in your project

For more information visit: https://littlevgl.com/font-converter

