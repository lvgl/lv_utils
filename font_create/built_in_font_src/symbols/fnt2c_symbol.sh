#!/usr/bin/env bash
#Example: sh ./fnt2c_symbol.sh my_symbol


python fnt2c.py -f $1_basic    -s 61440 -e 62190 -i 57344
python fnt2c.py -f $1_file     -s 61440 -e 62190 -i 57376
python fnt2c.py -f $1_feedback -s 61440 -e 62190 -i 57408
