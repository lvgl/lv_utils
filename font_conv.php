
<div class="row" style="margin-bottom:48px;">
	<div class="col-md-8 col-md-offset-2">
	    <p>With this free online font converter tool you can create C array from any TTF font. You can select a range of Unicode characters and speficy the bpp (bit-per-pixel).</p>
	    
	    <p>The font converter is designed to be compitible with <a hreaf="https://littlevgl.com" title="Open-source Embedded GUI Library">LittlevGL</a> but with minor modification you can make it compatible with other graphics libraries.</p>
	
		<h3>How to use the font converter?</h3>
		<ol>
		    <li>Choose a <strong>TTF font</strong></li>
		    <li>Give <strong>name</strong> to the output font. E.g. "arial_40"</li>
		    <li>Specify the <strong>height</strong> in px</li>
		    <li>Set the <strong>bpp</strong> (bit-per-piel). Higher value results smoother (anti-aliased) font</li>
		    <li>Set a <strong>range</strong> of Unicode character to include in your font</li>
		    <li>Opitonally <strong>pick some character</strong> from the Range to include only them. Useful for Asian fonts where characters are "sparse"</li>
		    <li>Use the <strong>Built-in</strong> option to include all bpp and add a <em>#if USE_FONT_NAME</em></li>
		    <li>Click the <strong>Convert</strong> button and result file will start to download.</li>
		</ol>
	
		<h3>How to use the generated fonts in LittlevGL?</h3>
		<ol>
		    <li>Copy the result C file into your LittlevGL project</li>
		    <li>In a C file of your application declare the font as: <span style="padding-left:10px;font-family:Courier;color:#115fad;">extern lv_font_t my_font_name;</span> or simply <span style="padding-left:10px;font-family:Courier;color:#115fad;">LV_FONT_DECLARE(my_font_name);</span></li>
		    <li>Set the font in a style:  <span  style="padding-left:10px;font-family:Courier;color:#115fad;">style.text.font = &amp;my_font_name;</span> </li>
		</ol>
	</div>
</div>

<div class="row">
	<div class="col-md-9 col-md-offset-2">
		<form action="/tools/font_conv_core.php" method="post" enctype="multipart/form-data" name="font_conv" onsubmit="return validate_font_conv_form()" style="padding:12px; border-radius:4px; border-style:solid; border-width:2px; border-color:#7babda">

			<div class="form-group row">
				<label for="font_file" class="form-label col-md-2">TTF file</label>
				<div class="col-md-10">   
					<input type="file" name="font_file" id="font_file" class="form-control-file" >
				</div>
			</div>


			<div class="form-group row">
				<label for="name" class="form-label col-md-2">Name</label>
				<div class="col-md-10">   
					<input type="text" name="name" id="name" class="form-control" placeholder="Name of output .c file (E.g: arial_40)">
				</div>
			</div>

			<div class="form-group row">
				<label for="height" class="col-md-2 col-form-label">Height</label>
				<div class="col-md-5">
					<input type="number" name="height" id="height" class="form-control" placeholder="Height in px">
				</div>
			</div>

			<div class="form-group row">
				<label for="bpp" class="col-md-2 col-form-label">Bpp</label>
				<div class="col-md-5">
					<input type="number" name="bpp" id="bpp" class="form-control" placeholder="Bit per pixel: 1, 2, 4 or 8">
				</div>
			</div>


			<div class="form-group row ">
				<label for="uni_first" class="form-label col-md-2">Range</label>
				<div class="col-md-5">    
					<input type="number" name="uni_first" id="uni_first" class="form-control" placeholder="First Unicode (E.g. 32)">
				</div>
				<div class="col-md-5">   
						<input type="number" name="uni_last" id="uni_last" class="form-control" placeholder="Last Unicode (E.g. 126)">
				</div>
			</div>

			<div class="form-group row">
				<label for="list" class="col-md-2 col-form-label">List</label>
				<div class="col-md-10">
					<input type="text" name="list" id="list" class="form-control" placeholder="Characters to conver (Optional). E.g. abc123ßÁŰ嗨Бπ (must be in Range)">
					<small>You can use HTML Numbers too. E.g. ABC : &amp;#65;&amp;#66;&amp;#67;<br>
					       Leave empty to pick all characters from the range</small>
				</div>
			</div>

			<div class="form-group row">
				<label for="built_in" class="col-md-2 col-form-label">Built-in</label>
				<div class="col-md-10">
					<input type="checkbox" name="built_in" id="built_in"> Convert as built-in font<br>
					<small>All bpp will be included<br>
					       The font can be enabled with "USE_FONT_NAME  1/2/4/8" (the number selects the bpp)</small>
				</div>  
			</div>
			
			<div class="form-group">
				<input type="submit" value="Convert" name="submit" class="btn btn-primary btn-lg">
			</div>
		</form>
		

		
		<h3>Useful notes</h3>
		<ul class="ul-space">
		    <li><strong>Unicode table</strong> to pick letters to the List: <a href="https://unicode-table.com/" target="_blank">https://unicode-table.com/</a></li>
		    <li><strong>Unicode ranges</strong> <a href="http://jrgraphix.net/research/unicode.php" target="_blank">http://jrgraphix.net/research/unicode.php</a></li>
		    <li><strong>7 px hight pixel font</strong> Syncronizer: <a href="/tools/synchronizer_nbp.ttf">Download</a> or visit its <a href="http://www.fontspace.com/total-fontgeek-dtf-ltd/synchronizer-nbp" target="_blank">Website</a>. </li>		    
		    <li><strong>8 px hight pixel font</strong> <span style="font-family: Synchronizer">Unscii</span>: <a href="/tools/unscii_8_mod.ttf">Download</a> (modifed) or visit its <a href="http://pelulamu.net/unscii/" target="_blank">Website</a>. </li>
		    <li><strong>Symbol font</strong> FontAwesome: <a href="/tools/fontawesome.ttf">Download</a> or visit its <a href="https://fontawesome.com/" target="_blank">Website</a>.</li>
		    <li><strong>List of built-in symbols: </strong> (Range: 61440 .. 62190)<br> 
		    &amp;#61441;&amp;#61448;&amp;#61451;&amp;#61452;&amp;#61453;&amp;#61457;&amp;#61459;&amp;#61460;&amp;#61461;&amp;#61465;<br>
            &amp;#61468;&amp;#61473;&amp;#61478;&amp;#61479;&amp;#61480;&amp;#61502;&amp;#61504;&amp;#61512;&amp;#61515;&amp;#61516;<br>
            &amp;#61517;&amp;#61521;&amp;#61522;&amp;#61523;&amp;#61524;&amp;#61543;&amp;#61544;&amp;#61553;&amp;#61556;&amp;#61559;<br>
            &amp;#61560;&amp;#61561;&amp;#61563;&amp;#61587;&amp;#61589;&amp;#61636;&amp;#61637;&amp;#61639;&amp;#61671;&amp;#61683;<br>
            &amp;#61724;&amp;#61732;&amp;#61787;&amp;#61931;&amp;#62016;&amp;#62017;&amp;#62018;&amp;#62019;&amp;#62020;&amp;#62099;</li>
		    <li>To learn more about the <strong>font handling of LittelvGL</strong> read this <a href="https://littlevgl.com/basics#fonts">Guide</a></li>
		    <li>To use the Fonts <strong>without LittlevGL</strong> copy the required parts of <a href="https://github.com/littlevgl/lvgl/blob/master/lv_misc/lv_font.c" target="_blank">lv_font.c</a> and <a href="https://github.com/littlevgl/lvgl/blob/master/lv_misc/lv_font.h" target="_blank">lv_font.h</a></li>
		</ul>
	</div>
</div>

<script>
function validate_font_conv_form() {
	var f = document.forms["font_conv"]["font_file"].value;
    
	if (f == "") {
        alert("A font file must be uploaded");
        return false;
    }

	var out_name = document.forms["font_conv"]["name"].value;
    
	if (out_name == "") {
        alert("Name must be filled out");
        return false;
    }
 	
	var h = document.forms["font_conv"]["height"].value;
    if (h == "") {
        alert("Height must be filled out");
        return false;
    }

	if (Number(h) < 4) {
        alert("Height must at least 4 px");
        return false;
    }

	if (Number(h) > 200) {
        alert("Height must smaller then 200 px");
        return false;
    }

	var bpp = document.forms["font_conv"]["bpp"].value;
    if (bpp == "") {
        alert("Bpp (bit per pixel) must be filled out");
        return false;
    }

	if (Number(bpp) != 1 && Number(bpp) != 2 && Number(bpp) != 4 && Number(bpp) != 8) {
        alert("Bpp (bit per pixel) can be 1, 2, 4 or 8");
        return false;
    }

	var uf = document.forms["font_conv"]["uni_first"].value;
	var ul = document.forms["font_conv"]["uni_last"].value;

	if (uf == "") {
        alert("In range: first unicode must be filled out");
		return false;
	}

	if (ul == "") {
        alert("In range: Last unicode must be filled out");
		return false;
	}

	if(Number(uf) < 32) {
       alert("In range: first unicode must be at least 32");
       return false;
	}

	if(Number(ul) < 32) {
       alert("In range: last unicode must be at least 32");
       return false;
	}

	if(Number(uf) > Number(ul)) {
       alert("In range: last unicode must be greater then first unicode");
       return false;
	}
}
</script>

