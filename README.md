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

**Clone or download** the repository: git clone https://github.com/littlevgl/utils.git

### Image converter
1. Copy an image to img_conv folder
2. Open a Terminal and go to this directory
3. Type to the Terminal: `python img_cony.py test.png 16 1`. Parameters:
   * Image file name
   * Color depth: 8/16/24
   * Set Transp. bit in the header or not (it means piselc with a specific are not drawn)
4. Check the output *img_test.c* and *img_test.bin*

For more iformation visit http://www.gl.littlev.hu/image-converter/

### Font converter
1. Install **BMfont** with font_create/install_bmfont_1.13.exe
2. Open BMfont:
   1. In **Options/Font settings** choose a font, set its size and other settings (bold, italian etc.)
   2. In **Options/Export settings** choose XML file format and .png texture. Set the texture width and height to 512.
   3. Save the *fnt* file: **Options/Save bitmap font as…**
3. According to **font_create/test_conf** create/modify a configuration file:
   * **CFontName** The file name of the .fnt file without extension
   * **BytesWidth** How much bytes are needed to represent the full width of the letters. One pixel means one bit, so 2 bytes are enough for maximum 16 pixel width.
   * **BytesHeight** The same as the font height in Font settings of BMFont.
   * **FirstAscii** and **LastAscii** the range of letters.
4. Be sure the *fnt* file, the configuration file and fnt2py.py are in the same folder
5. In Terminal go to this folder and type: `python fnt2py.py font_conf`
6. Check the generated *.c* and *h* files

For more information visit: http://www.gl.littlev.hu/font-import/
