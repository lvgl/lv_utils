#!/user/bin/env python

'''
Small utility that converts 1bpp BDF fonts to a font compatible with littlevgl.
'''

# General imports
import argparse
import math


class Glyph:
    pixel_art_0 = '.'
    pixel_art_1 = '%'
    def __init__(self, props):
        if props is None:
            self.name = 'MISSING'
            self.encoding = 0
            self.swidth = (0, 0)
            self.dwidth = (0, 0)
            self.bbx = (0, 0, 0, 0)
            self.bitmap = None
        else:
            for k, v in props.items():
                setattr(self, k, v)

    def __lt__(self, other):
        return self.encoding < other.encoding

    def __repr__(self,):
        return "<Glyph: %s 0x%08X>" % (self, self.encoding)

    def __str__(self,):
        return self.name

    def get_width(self):
        return self.dwidth[0]

    def get_byte_width(self):
        # Compute how many bytes wide the glyph will be
        return math.ceil(self.get_width() / 8.0)

    def get_height(self):
        if self.bitmap is None:
            return 0
        else:
            return len(self.bitmap)

    def get_encoding(self):
        return self.encoding

    def write_bitmap(self, f):
        if self.bitmap is None:
            return

        f.write('''/*Unicode: U+%04x ( %s ) , Width: %d */\n''' %
                (self.encoding, self.name, self.dwidth[0]) )
        for line in self.bitmap:
            pixel_art = ' //'
            for i in range(0, len(line), 2):
                # Parse Pixel Art
                bits = bin( int(line[i:i+2], 16) )[2:].zfill(8)
                bits = bits.replace('0', self.pixel_art_0)
                bits = bits.replace('1', self.pixel_art_1)
                pixel_art += bits

                # Parse Hex
                f.write("0x%s, " % line[i:i+2])
            f.write(pixel_art)
            f.write("\n")
        f.write("\n\n")

    def shift_up(self, y):
        self.bitmap += ['0'*len(self.bitmap[0]),]*y

    def pad_top_to_height(self, height):
        pad_n = height - self.get_height()
        self.bitmap = ['0'*len(self.bitmap[0]),] * pad_n + self.bitmap

    def apply_bbx_x(self):
        w = self.bbx[0]
        x = self.bbx[2]

        # Naive and Inefficient, but works
        new_bitmap = []
        for row in self.bitmap:
            # Convert to ascii string of 1's and 0's
            binary = bin(int(row, 16))[2:].zfill(8)
            # Shift to the right specified by bbx
            binary = '0'*x + binary
            # Convert it back into an ascii hex string
            new_row = ''
            for i in range(0,self.get_width(), 8):
                new_row += "%02X" % int(binary[i:i+8][::-1].zfill(8)[::-1],2)
            new_bitmap.append(new_row)

        self.bitmap = new_bitmap

def parse_bdf(fn):
    # Converts bdf file to a list of Glyphs
    # Read in BDF File
    with open(fn, "r") as f:
        bdf = f.readlines()
    bdf = [x.rstrip("\n") for x in bdf]

    # Iterate through BDF Glyphs
    glyphs = []
    i = -1
    while i < len(bdf):
        props = {}

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] == 'ENDFONT':
            break;
        if tokens[0] != "STARTCHAR":
            continue
        props['name'] = tokens[1]

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] != "ENCODING":
            continue
        encoding = int(tokens[-1])
        if encoding < 0: # skip glyphs with a negative encoding value
            continue
        props['encoding'] = encoding

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] != "SWIDTH":
            continue
        props['swidth'] = (int(tokens[1]), int(tokens[2]))

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] != "DWIDTH":
            continue
        props['dwidth'] = (int(tokens[1]), int(tokens[2]))

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] != "BBX":
            continue
        props['bbx'] = (int(tokens[1]), int(tokens[2]),
                int(tokens[3]), int(tokens[4]))

        i += 1
        tokens = bdf[i].split(' ')
        if tokens[0] != "BITMAP":
            continue

        props['bitmap'] = []
        while True:
            i += 1
            tokens = bdf[i].split(' ')
            if tokens[0] == 'ENDCHAR':
                break
            props['bitmap'].append(tokens[0])
        if len(props['bitmap']) == 0:
            props['bitmap'] = ['00',]
        glyphs.append(Glyph(props))
    return glyphs

def apply_bbx(glyphs):
    '''
    Shifts the glyphs to the write position and makes them a uniform
    height.
    '''
    # Add appropriate spacing to make uniform height
    # first, prepend from the bottom
    offsets_x = []
    offsets_y = []
    for glyph in glyphs:
        offsets_x.append(glyph.bbx[2])
        offsets_y.append(glyph.bbx[3])

    offsets_y_min = min(offsets_y)
    offsets_y_max = max(offsets_y)

    for glyph in glyphs:
        glyph.shift_up(glyph.bbx[3] - offsets_y_min)

    # Now pad from the top
    heights = []
    for glyph in glyphs:
        heights.append(glyph.get_height())
    max_height = max(heights)
    for glyph in glyphs:
        glyph.pad_top_to_height(max_height)

    # now shift according to bounding box
    for glyph in glyphs:
        glyph.apply_bbx_x()

    return glyphs

def parse_args():
    ''' Parse CLI arguments into an object and a dictionary '''
    parser = argparse.ArgumentParser()
    parser.add_argument('bdf_fn', type=str, default='imgs',
            help='BDF Filename')
    parser.add_argument('font_name', type=str, default='font_name',
            help='Name of the font to be generated')
    parser.add_argument('--toggle', '-t', action='store_true',
            help='''Wrap entire file in "#if USE_LV_FONT_" macro''')
    parser.add_argument('--ascii', action='store_true',
            help='''Limit exported range to 0-127''')
    args = parser.parse_args()
    dargs = vars(args)
    return (args, dargs)

def main():
    args, dargs = parse_args()

    glyphs = parse_bdf(args.bdf_fn)
    glyphs.sort() # Sorts by encoding (utf8) value

    if args.ascii:
        ascii_glyphs = []
        for glyph in glyphs:
            if( glyph.encoding <= 127 ):
                ascii_glyphs.append(glyph)
        glyphs = ascii_glyphs

    glyphs = apply_bbx(glyphs)

    ##########################################
    # fill in dummies for unavailable glyphs #
    ##########################################
    new_glyphs = []
    glyph_index = 0;
    for i in range(glyphs[0].get_encoding(), glyphs[-1].get_encoding()):
        if(glyphs[glyph_index].get_encoding() == i):
            new_glyphs.append(glyphs[glyph_index])
            glyph_index += 1
        else:
            new_glyphs.append(Glyph(None))
            new_glyphs[-1].encoding = i
    glyphs = new_glyphs

    ################
    # WRITE HEADER #
    ################
    out = open(args.font_name + '.c', "w")
    out.write('''
#include "../lv_misc/lv_font.h"
''')

    if args.toggle:
        out.write('''
#if USE_LV_FONT_%s != 0  /*Can be enabled in lv_conf.h*/
''' % args.font_name.upper())

    #################
    # WRITE BITMAPS #
    #################
    out.write(
'''
static const uint8_t %s_glyph_bitmap[] =
{
''' % args.font_name)

    for glyph in glyphs:
        glyph.write_bitmap(out)

    out.write('''
};
''')

    ######################
    # WRITE DESCRIPTIONS #
    ######################
    out.write(
'''
/*Store the glyph descriptions*/
static const lv_font_glyph_dsc_t %s_glyph_dsc[] =
{
''' % args.font_name)

    glyph_index = 0
    for glyph in glyphs:
        out.write("{.w_px = %d, .glyph_index = %d}, /*Unicode: U+%04x ( %s )*/\n" \
                % (glyph.get_width(), glyph_index, glyph.get_encoding(), glyph) )
        glyph_index += glyph.get_byte_width() * glyph.get_height()

    out.write('''
};
''')

    #####################
    # WRITE FONT STRUCT #
    #####################
    out.write(
'''
lv_font_t lv_font_%s =
{
    .unicode_first = %d,	/*First Unicode letter in this font*/
    .unicode_last = %d,	/*Last Unicode letter in this font*/
    .h_px = %d,				/*Font height in pixels*/
    .glyph_bitmap = %s_glyph_bitmap,	/*Bitmap of glyphs*/
    .glyph_dsc = %s_glyph_dsc,		/*Description of glyphs*/
    .glyph_cnt = %d,			/*Number of glyphs in the font*/
    .unicode_list = NULL,	/*Every character in the font from 'unicode_first' to 'unicode_last'*/
    .get_bitmap = lv_font_get_bitmap_continuous,	/*Function pointer to get glyph's bitmap*/
    .get_width = lv_font_get_width_continuous,	/*Function pointer to get glyph's width*/
    .bpp = 1,				/*Bit per pixel*/
    .monospace = 0,				/*Fix width (0: if not used)*/
    .next_page = NULL,		/*Pointer to a font extension*/
};

''' % (args.font_name,         # struct name
    glyphs[0].get_encoding(),  # first utf8 encoded value
    glyphs[-1].get_encoding(), # last utf8 encoded value
    glyphs[0].get_height(),    # height of each glyph
    args.font_name,            # glyph_bitmap
    args.font_name,            # glyph_dsc
    len(glyphs),               # glyph_cnt
    ) )

    if args.toggle:
        out.write("#endif")
    out.close()

if __name__=='__main__':
    main()
