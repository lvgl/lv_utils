#!/usr/bin/env python
from struct import *
from PIL import Image as image
import ntpath
import sys, getopt, os.path

cd = "16"
transp = "0"
fn = ""

def conf_init(argv):
    global fn
    global cd
    global transp
    
    try:
        opts, args = getopt.getopt(argv, "f:c:th",["file=","colordepth=","chromakey=","help"])
    except getopt.GetoptError:
        print 'Usage: python img_conv.py -f <file name> [-c <color depth> -t <chroma key>]' 
        sys.exit(2)
    for opt, arg in opts:
        if opt in ('-h', '--help'):
            print 'Usage' 
            print '   python img_conv.py -f <file name> [-c <color depth> -t <chroma key>]\n'
            print 'Options' 
            print '  -f, --file         name of image to convert(e.g. my_pic.png)'
            print '  -c, --colordepth   color depth for the binary file, 8/16/24.                       Optional, default: 16 '
            print '  -t, --chromakey    mark as chrmakeyed (no value) needed                            Optional, default: off'
            print 'Example' 
            print '  Convert an image with 24 bit color depth and no chroma key'              
            print '    python img_conv.py -f file1.png -c 24\n'
            print '  Convert an image with 16 bit color depth and chrom akey'
            print '    python img_conv.py -f file2.png -c 16 -t'
            sys.exit()
        elif opt in ("-f", "--file"):
            fn = arg
        elif opt in ("-c", "--colordepth"):
            cd = arg
        elif opt in ("-t", "--chromakey"):
            transp = "1"


    if fn == "" : 
        print "ERROR: No image file specifeied"
        print 'img_conv.py -f <file name> [-c <color depth> -t <chroma key>]\n'
        exit()

    if os.path.exists(fn) == False:
        print "ERROR: " + fn + " not exists"
        exit()
        
    if cd != "8" and cd != "16" and cd != "24":
        print "ERROR: " + cd + " is an invalid color depth. Use 8, 16 or 24"
        exit()     
  
def img_proc():
    global fn
    global cd
    global transp
    
    #Open image
    try:
        img = image.open(fn)
    except IOError as ioe:
        print "ERROR:", ioe 
        print "ERROR: Try to convert the image to .jpg or .png format"
        exit()
        
    (w,h) = img.size 
    data = list(img.getdata())
    print "Size: ",  w, "x", h
    print "Bin. color depth: " + cd
    print "Chroma keyed: " + transp
    print "-----------------"

    #Create the output text file
    fn_base = ntpath.basename(fn)
    fn_base = os.path.splitext(fn_base)[0]
    fn_txt = "img_" + fn_base + ".c"
    f_txt = open(fn_txt, 'w')

    #Write txt to the header
    inc = "#include <stdint.h> \r\n#include \"lvgl/lv_misc/lv_color.h\"\r\n\r\n"
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
    txt_c8 = ""
    txt_c16 = ""
    txt_c24 = ""

    dsc = "\r\n"
    dsc +=  "/* Width = " + str(w) + "\r\n"
    dsc +=  " * Height = " + str(h) + "\r\n"  
    dsc +=  " * Chroma keyed: " + transp + "\r\n"
    dsc +=  " * Color depth: " + cd + "\r\n"
    dsc +=  " */\r\n\r\n"

    f_txt.write(dsc)   

    f_txt.write("const lv_color_int_t img_" + fn_base + "[] = {\r\n")
    
    txt_c8 += "#if LV_COLOR_DEPTH == 1 || LV_COLOR_DEPTH == 8\r\n  " + str(header & 0xFF) + ", " + str((header >> 8) & 0xFF) + ", " + str((header >> 16) & 0xFF) + ", " + str((header >> 24) & 0xFF) + ","
    txt_c8 += "  /* HEADER */\r\n\r\n  /*IMAGE DATA*/\r\n  "
    txt_c16 += "#elif LV_COLOR_DEPTH == 16\r\n  " + str(header & 0xFFFF) + ", " + str((header >> 16) & 0xFFFF) + ","
    txt_c16 += "  /* HEADER */\r\n\r\n  /*IMAGE DATA*/\r\n  "
    txt_c24 += "#elif LV_COLOR_DEPTH == 24\r\n  " + str(header) + ","
    txt_c24 += "  /* HEADER */\r\n\r\n  /*IMAGE DATA*/\r\n  "

    print "Converting... "

    px_out = 0
    col = 0
    line = 0
    for px in data:
        try:
            # 8 bit colors
            r =  px[0] >> 6
            g =  px[1] >> 5
            b =  px[2] >> 5
            px_out = (r << 5) + (g << 2) + b
            txt_c8 += str(px_out) + ", "
            if cd == "8": f_bin.write(pack('<B', px_out))

            # 16 bit colors
            r =  px[0] >> 3
            g =  px[1] >> 2
            b =  px[2] >> 3
            px_out = (r << 11) + (g << 5) + b
            txt_c16 += str(px_out) + ", "
            if cd == "16": f_bin.write(pack('<H', px_out))
            
            # 24 bit colors
            r =  px[0]
            g =  px[1]
            b =  px[2]
            px_out = (r << 16) + (g << 8) + b
            txt_c24 += str(px_out) + ", "
            if cd == "24": f_bin.write(pack('<L', px_out))
        
        except TypeError as te:
            print "ERROR:", te 
            print "ERROR: Convert the image to .jpg or .png format and try again!"
            exit()

        col += 1
        if col % w == 0:    #Start a new line in c file
            line += 1
            txt_c8 += "\r\n  "
            txt_c16 += "\r\n  "
            txt_c24 += "\r\n  "
            sys.stdout.write("\r%d%%" % ((line * 100) / h)) #Wirte percentage
            sys.stdout.flush()
    
    print ""		#New line after percentage count

    txt_c8 += "\r\n\r\n"
    txt_c16 += "\r\n\r\n"
    txt_c24 += "\r\n#else\r\n"
    

    #Write the c array
    f_txt.write( txt_c8 + txt_c16 + txt_c24)
    f_txt.write('/*Color depth check*/\r\n#error  "Invalid color depth"\r\n#endif')
    f_txt.write("\r\n};  /*Arrray end*/")


    #Close files
    f_txt.close()
    f_bin.close()

    print "Conversion is ready"
    print "Data is written into: img_" + fn_base + ".c and img_" + fn_base + ".bin"
    print "-----------------"
    print "FINISHED"  


def main(argv):
    print "-------------------------------"
    print "Image converter for LittlevGL"
    print "-------------------------------"
    conf_init(argv)
    img_proc()        

if __name__ == "__main__":
    main(sys.argv[1:])  
    
def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)
