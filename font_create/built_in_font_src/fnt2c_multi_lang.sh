#!/usr/bin/env bash
#Example: sh ./fnt2c_multi_lang.sh my_font
#It will generate:
#ASCII
#Latin supplement (as *_sup)
#Latin extended A (as *_latin_ext_a)
#Latin extended B (as *_latin_ext_b)
#Cyrillic (as *_cyrillic)

python fnt2c.py -f $1 -o $1 -s 32 -e 126
python fnt2c.py -f $1 -o $1_sup -s 160 -e 255
python fnt2c.py -f $1 -o $1_latin_ext_a -s 256 -e 383
python fnt2c.py -f $1 -o $1_latin_ext_b -s 384 -e 591
python fnt2c.py -f $1 -o $1_cyrillic -s 1024 -e 1279

