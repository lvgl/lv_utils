#!/usr/bin/env bash
python fnt2c.py -f $1 -o $1 -s 32 -e 126
python fnt2c.py -f $1 -o $1_sup -s 160 -e 255
python fnt2c.py -f $1 -o $1_latin_ext_a -s 256 -e 383
python fnt2c.py -f $1 -o $1_latin_ext_b -s 384 -e 591
python fnt2c.py -f $1 -o $1_cyrillic -s 1024 -e 1279
