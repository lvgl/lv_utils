# Utilities
Utilities to convert 
* pictures
* operation system fonts 

to **raw C arrays**.

The utilities are written to be compatible with the **Littlev Graphics Library**. 

GitHub: https://github.com/littlevgl/lvgl
Website: http://www.gl.littlev.hu

## Usage
Both of the utilities are using Python scripts. So you has to install Python on your operation system.

**Clone or download** the repository: `git clone https://github.com/littlevgl/lv_utils.git`

### Image converter
1. Copy an image to img_conv folder
2. Open a Terminal and go to this directory
3. Type to the Terminal: `python img_cony.py -f test.png -c 16` Parameters:
   * **-f** file to convert
   * **-c** Color depth: 8/16/24
   * **-t** Optional to mark the image as chroma keyed (LV_COLOR_TRANSP pixel will be transparent)
4. Check the output *img_test.c* and *img_test.bin*. The C file contains the data for all color depths but the binary file is only for the color depth specified by **-c**

For more iformation visit http://www.gl.littlev.hu/image-converter/

### Font converter
1. Install **BMfont** with font_conv/install_bmfont_1.13.exe
2. Open BMfont:
   1. In **Options/Font settings** choose a font, set its size and other settings (bold, italian etc.)
   2. In **Options/Export settings** choose XML file format and .png texture. Set the texture width and height to 512.
   3. Save the *fnt* file: **Options/Save bitmap font as…**
3. Copy the craeted  *fnt* and *.png* file nex to the *fnt2py.py*
4. In Terminal go to this folder and type: `python -f my_font -s 32 -e 126 -o my_font_ascii` Parameters:
   - **-f** font name without extension
   - **-s** first unciode character to convert
   - **-e** last unciode character to convert
   - **-o** output font file name
6. Check the generated *.c* and *h* files and use them in your project

For more information visit: http://www.gl.littlev.hu/font-converter/
