#!/usr/bin/env bash
#Example: sh ./fnt2c_symbol.sh my_symbol

#It will generate:
#  - Symbol font with 30 px height (as *_30)
#  - Symbol font with 60 px height (as *_60) 

python fnt2c.py -f $1_30 -s 90 -e 122
python fnt2c.py -f $1_60 -s 90 -e 122
