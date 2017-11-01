#!/usr/bin/env python

from PIL import Image as image
from struct import *
import sys
import ntpath
import os
import time

cd = "16"
transp = "0"

if len(sys.argv) < 2:
  print "No inputfile. Usage: ", sys.argv[0], " filename [OPTIONAL color depth (8/16/24)]"
  exit()
else: 
  fn = sys.argv[1]
  if len(sys.argv) >= 3:
    cd = sys.argv[2]
  else:
    print "Auto Color depth: 16"

  if len(sys.argv) >= 4:
    if(sys.argv[3] == "0"):
 	   transp = "0"
    else:  
      transp = "1"
  else:
    print "Auto Transp: 0"
 

  if len(sys.argv) >= 5:
    print "Too much argument. Usage: ", sys.argv[0], " filename [OPTIONAL color depth (8/16/24)]"
    exit()

if os.path.exists(sys.argv[1]) == False:
  print "---ERROR: File not exists: ", fn
  exit()

#Open image
try:
  img = image.open(sys.argv[1])
except IOError as ioe:
  print "---ERROR:", ioe 
  print "---ERROR: Try to convert the image to jpg or png format"
  exit()
  

(w,h) = img.size 
data = list(img.getdata())
print "Size: ",  w, "x", h

#Create the output text file
fn_base = ntpath.basename(fn)
fn_base = os.path.splitext(fn_base)[0]
fn_txt = "img_" + fn_base + ".c"
f_txt = open(fn_txt, 'w')

#Write txt the header
inc =  '#include "img_conf.h" \r\n'
inc += "#if USE_IMG_"+ fn_base.upper() + " != 0 \r\n\r\n"
inc += "#include <stdint.h> \r\n#include \"misc/gfx/color.h\"\r\n\r\n"
inc += "#if COLOR_DEPTH != " + cd + "\r\n#error \"img_" + fn_base + ": Color depth mismatch\"\r\n#else\r\n\r\n"
f_txt.write(inc)

#Create the output bin file
fn_bin = "img_" + fn_base + ".bin"
f_bin = open(fn_bin, 'wb')

cd_bin = 0;
if(cd == "8"): 
  cd_bin = 1;
if(cd == "16"): 
  cd_bin = 2;
if(cd == "24"): 
  cd_bin = 3;

transp_bin = 0;
if(transp == "1"):
  transp_bin = 1;

header = (w & 0xFFF) | ((h & 0xFFF) << 12) | ((transp_bin & 0x1) << 24) | ((cd_bin & 0x3) << 25);

#Write bin header
f_bin.write(pack('<I', header))

#Write the c header
f_txt.write("const color_int_t img_" + fn_base + "[] = {\r\n")
dsc = "/*HEADER\r\n   Width = " + str(w) + "\r\n   Height = " + str(h) + "\r\n   Transp: " + transp + "\r\n   Color depth: " + cd + "*/\r\n"
if cd == "8":
  dsc +=  str(header & 0xFF) + ", " + str((header >> 8) & 0xFF) + ", " + str((header >> 16) & 0xFF) + ", " + str((header >> 24) & 0xFF) + ","  
elif cd == "16":
  dsc += str(header & 0xFFFF) + ", " + str((header >> 16) & 0xFFFF) + ","
elif cd == "24":
  dsc += str(header) + ","
else:
  print "Invalid color depth"
  exit()

dsc += "\r\n\r\n/*IMAGE DATA*/ \r\n\r\n"
f_txt.write(dsc)

print "Converting... "

num = 0
px_out = 0
col = 0
line = 0
for px in data:
  try:
    if cd == "8":
      r =  px[0] >> 6
      g =  px[1] >> 5
      b =  px[2] >> 5
      px_out = (r << 5) + (g << 2) + b
      f_bin.write(pack('<B', px_out))
    elif cd == "16":
      r =  px[0] >> 3
      g =  px[1] >> 2
      b =  px[2] >> 3
      px_out = (r << 11) + (g << 5) + b
      f_bin.write(pack('<H', px_out))
    elif cd == "24":
      r =  px[0]
      g =  px[1]
      b =  px[2]
      px_out = (r << 16) + (g << 8) + b
      f_bin.write(pack('<L', px_out))
    else:
      print "Invalid color depth"	
      exit()
  except TypeError as te:
    print "---ERROR:", te 
    print "---ERROR: Convert the image to jpg format and try again!"
    exit()

  f_txt.write(str(px_out))
  f_txt.write(", ")

  col += 1
  if col % w == 0:
    line += 1
    f_txt.write("\r\n")
    sys.stdout.write("\r%d%%" % ((line * 100) / h)) #Wirte percentage
    sys.stdout.flush()

print ""		#New line after % count

f_txt.write("};\r\n\r\n")
f_txt.write("#endif\t/*Color depth check*/\r\n")

f_txt.write("#endif\t/*Image enable*/\r\n")

#Close files
f_txt.close()
f_bin.close()

print "Conversion is ready"
print "Data is written into", fn
print "---------------------"
print "FINISHED"  

def path_leaf(path):
  head, tail = ntpath.split(path)
  return tail or ntpath.basename(head)
