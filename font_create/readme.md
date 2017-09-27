# Introduction
fnt2c.py reads bitmap font output from the Bitmap Font Generator by
AngelCode: http://www.angelcode.com/products/bmfont/  and outputs byte table 
arrays in C language which is compatible with LittlevGL fonts.

In BMFont set the following:
- Options/Export settings:
 - Width and Height: 2048
 - Font descriptor: XML
 - Texture: png

# Usage
 python fnt2c.py -f <font_name> [-o <output file> -s <start unicode> -e <end unicide>]

## Options
  -f, --font    name of the font file without any extension (e.g. arial_10)
  -o, --output  name of the output file without any extension (e.g. arial_10_cyrillic).   Optional, default: font name
  -s, --start   first unicode charater to convert (e.g. 1024).                            Optional, default: 32
  -e, --end     last unicode charater to convert (e.g. 1279).                             Optional, default: 126

## Example
Convert the ASCII characters from dejavu_20.fnt/png and save to devaju_20.c/h
`python fnt2c.py -f dejavu_20`

Convert the Cyrillic code page from dejavu_20.fnt/png and save to devaju_20_cyrillic.c/h
`python fnt2c.py -f dejavu_20 -o dejavu_20_cyrillic -s 1024 -e 1279`

