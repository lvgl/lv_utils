# coding: utf-8

# USAGE EXAMPLE:
# --------------
#
# ruby hex2lvfont.rb unscii-16.hex -r basic -h 16 -n lv_font_unscii_16
#
#

require 'optparse'


PROGNAME = $0.split('/')[-1]

RANGE_ALIAS = {
    'basic'     => [ 0x0020, 0x007e ],
    'latin_sup' => [ 0x00a0, 0x00ff ],
    'cyrillic'  => [ 0x0410, 0x044f ],
    'all'       => [ 0x0000, 0xffff ],
}

#
# Command line parsing
#

$opts = {
    :range  => 0x0020 .. 0x007e,
    :set    => nil,
    :height => nil,
    :name   => nil,
    :bpp    => 1,
}
    
OptionParser.new do |opts|
    opts.banner = "Usage: #{PROGNAME} [options] font.hex"

    opts.on("-r", "--range [RANGE]", "Range selection") do |r|
        if r = case r
               when /^(\d+)\.\.(\d+)$/ then [ $1.to_i, $2.to_i ]
               when /^(.)-(.)$/        then [ $1,      $2      ]
               else RANGE_ALIAS[r]
               end
            f, l = r.sort
            $opts[:range ] = f .. l
        else
            warn "Invalid range provided, can be:"
            warn "  - character range: a-z"
            warn "  - integer range  : 32..127"
            warn "  - alias          : basic, latin_sup, cyrillic"
            exit
        end
    end
    opts.on("-s", "--set [STRING]",  "Set selection") do |s|
        $opts[:set] = s.split('')
    end
    opts.on("-b", "--bpp [1,2,4,8]", Integer, "Bit-pet-pixel") do |b|
        if [ 1, 2, 4, 8 ].include?(b)
            $opts[:bpp] = b
        else
            warn "Only BPP of 1, 2, 4 or 8 are supported"
            exit
        end
    end
    opts.on("-n", "--name [STRING]", "Font name") do |n|
        $opts[:name] = n
    end
    opts.on("-H", "--height [INTEGER]", Integer, "Font height") do |h|
        $opts[:height] = h
    end
end.parse!

$file = ARGV[0]

if $opts[:name].nil?
    $opts[:name] = $file.split('/')[-1]
                        .sub(/\.[^.]+/, '')
                        .gsub(/[^a-z0-9_]/, '_')
end

if $opts[:height].nil?
    warn "Original font height need to be specified"
    exit
end

if $opts[:bpp] != 1
    warn "For now only 1 BPP is supported"
    exit
end


#
# FontConverter
#

class FontConverter
    def self.unicode(c)
        "Unicode: U+%04X (%s)" % [
            c, c > 32 ? c.chr(Encoding::UTF_8) : '[]'
        ]
    end
    
    def read(fontfile, height)
        regexp  = /^(?<code>[0-9a-f]+):(?<data>[0-9a-f]+)$/i
        @height = height
        @glyphs = Hash[File.foreach(fontfile).with_index.map {|line, idx|
                           unless m = regexp.match(line)
                               warn "Parsing error line #{idx} (skipping glyph)"
                               next
                           end
            
                           [ m[:code].to_i(16), [ m[:data] ].pack('H*') ]
                       }]
        @list   = @glyphs.keys
    end

    def filter(range: nil, set: nil)
        return if range.nil? && set.nil?
        
        @list  = []
        @list += set.to_a    if set
        @list += range.to_a  if range
        @list  = @list.map {|i| case i
                                when String  then i.ord
                                when Integer then i
                                end }
                     .compact.sort.uniq
        @list.reject! {|code| code < 32 } # Reject control characters
        @list &= @glyphs.keys             # Ensure that glyphs exist
    end

    def build(bpp = [ 1 ])
        lists = @list.slice_when {|a,b| (a+1) != b }
                     .map {|list| list.first .. list.last }
        @sparse = lists.size > 1
        
        if @sparse
            warn "Glyph selection result in a sparse font"
            warn "  => increasing size by #{4 * (@list.size+1)} bytes"
        end

        @bpp    = bpp
        @c_data = {}
        bpp.each {|b|
            index          = 0;
            @c_data[b] = d = {}
            
            @list.each {|code|
                data       = @glyphs[code]
                bitstring  = data.unpack1("B*")
                width      = bitstring.size / @height
                text       = bitstring.tr('01', '.#').scan(/.{#{width}}/)
                bytestring = data.unpack("C*")
                data       = bytestring.each_slice(width/8).map {|row|
                    row.map {|b| "0x%02x" % b }.join(', ') }
            
                d[code] = {
                    :width => width,
                    :text  => text,
                    :data  => data,
                    :index => index,
                }
                
                index += bytestring.size
            }
        }
    end

    def header(name)
        puts "#include <lvgl/lv_misc/lv_font.h>"
        puts ""
        puts "#if USE_#{name.upcase} != 0   /* Can be enabled in lv_conf.h */"
    end

    def footer(name)
        puts ""
        puts "#endif"
    end
    
    def output(name)
        puts ""
        puts "/* Store the image of the letters (glyph) */"
        puts "static const uint8_t #{name}_glyph_bitmap[] = "
        puts "{"

        @bpp.each {|b|
            puts "#if USE_#{name.upcase} == #{b}"
            @c_data[b].each {| code, width:, text:, data:, index: |
                unicode = self.class::unicode(code)
                puts "  /* #{unicode}, Width: #{width} */"
                0.upto(@height-1) {|i|
                    puts "  %s,  // %s" % [ data[i], text[i] ]
                }
                puts
                puts                
            }
            puts "#endif"
        }
        puts "};"
        
        # Glyph description
        puts ""
        puts "/* Store the glyph descriptions */"
        puts "static const lv_font_glyph_dsc_t #{name}_glyph_dsc[] = "
        puts "{"
        @bpp.each {|b|
            puts "#if USE_#{name.upcase} == #{b}"
            @c_data[b].each {| code, width:, index:, ** |
                unicode = self.class::unicode(code)
                puts "  { .w_px = %2d,  .glyph_index = %4d },    /* %s */" % [
                         width, index, unicode ]
            }
            puts "#endif"
        }                
        puts "};"
        
        if @sparse
            # Unicode mapping
            puts ""
            puts "/* List of unicode characters */"
            puts "static const uint32_t #{name}_unicode_list[] = {"
            @list.each {|code|
                unicode = self.class::unicode(code)
                puts "  %4d,    /* %s */" % [ code, unicode ] 
            }
            puts "     0,    /* End indicator */"
            puts "};"
            
            unicode_list = "#{name}_unicode_list"
            get_bitmap   = "lv_font_get_bitmap_sparse"
            get_width    = "lv_font_get_width_sparse"
        else
            unicode_list = "NULL"
            get_bitmap   = "lv_font_get_bitmap_continuous"
            get_width    = "lv_font_get_width_continuous"
        end

        puts ""
        puts "lv_font_t #{name} = "
        puts "{"
        puts "    .unicode_first = #{@list.first},"
        puts "    .unicode_last  = #{@list.last},"
        puts "    .h_px          = #{@height},"
        puts "    .glyph_bitmap  = #{name}_glyph_bitmap,"
        puts "    .glyph_dsc     = #{name}_glyph_dsc,"
        puts "    .unicode_list  = #{unicode_list},"
        puts "    .get_bitmap    = #{get_bitmap},"
        puts "    .get_width     = #{get_width},"
        @bpp.each {|b|
            puts "#if USE_#{name.upcase} == #{b}"        
            puts "    .bpp           = #{b},"
            puts "#endif"
        }
        puts "    .next_page     = NULL,"
        puts "};"
    end
end



$fc = FontConverter.new
$fc.read($file, $opts[:height])
$fc.filter(range: $opts[:range], set: $opts[:set])

$fc.header($opts[:name])
$fc.build([1])
$fc.output($opts[:name])
$fc.footer($opts[:name])
