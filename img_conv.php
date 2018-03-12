
<div class="row" style="margin-bottom:48px;">
	<div class="col-md-8 col-md-offset-2">
	 <p>With this free online image converter tool you can create C arrays or raw binary files from images. PNG, JPG and BMP files are supported. To handle trasparency you can add an alpha byte for every pixel or mark the image as chroma keyed. </p>
	    
	    <p>The image converter is designed to be compitible with <a hreaf="https://littlevgl.com" title="Open-source Embedded GUI Library">LittlevGL</a> but with minor modification you can make it compatible with other graphics libraries.</p>
	
	<h3>How to use the image converter?</h3>
		<ol>
		    <li>Choose an <strong>image</strong> (png, jpg, or bmp)</li>
		    <li>Give a <strong>name</strong> to the output file. E.g. "wallpaper1"</li>
		    <li>Specify the <strong>trasparency</strong> type
		        <ul>
		        <li>None: just convert the image as it is </li>
		        <li>Alpha byte: add alpha byte to every pixel to handle pixel level opacity</li>
		        <li>Chroma keyed: pixels with LV_COLOR_TRANSP (lv_conf.h) will be transparent</li>
		        </ul>
		    <li>Set the <strong>output format</strong>: C array, Binary RGB332, Binary RGB565, Binary RGB888 </li>
		    <li>Click the <strong>Convert</strong> button and result file will start to download.</li>
		</ol>

	<h3>How to use the generated file in LittlevGL?</h3>
		<ul>
	    <li>For C arrays
	        <ol>
	        <li>Copy the result C file into your LittlevGL project</li>
	        <li>In a C file of your application declare the image as: <span style="padding-left:10px;font-family:Courier;color:#115fad;">extern const lv_img_t my_image_name;</span> or <span style="padding-left:10px;font-family:Courier;color:#115fad;">LV_IMG_DECLARE(my_image_name);</span></li>
	        <li>Set the image for an <em>lv_img</em> object:  <span  style="padding-left:10px;font-family:Courier;color:#115fad;">lv_img_set_src(img1, &my_image_name);</span> </li>
	        </ol>
	    </li>
	    <li>For externally binary files (e.g. SD card)
	        <ol>
	        <li>Set up a new driver. To learn more read the <a href="https://github.com/littlevgl/lv_examples/blob/master/lv_tutorial/6_images/lv_tutorial_images.c">Tutorial</a>.</li>
	        <li>Set the image for an <em>lv_img</em> object:  <span  style="padding-left:10px;font-family:Courier;color:#115fad;">lv_img_set_src(img1, "S:/path/to/image");</span> </li>
	        </ol>
	    </li>
	    </ol>
	</div>
</div>

<div class="row">
	<div class="col-md-9 col-md-offset-2">
		<form action="/tools/img_conv_core.php" method="post" enctype="multipart/form-data" name="img_conv" onsubmit="return validate_img_conv_form()" style="padding:12px; border-radius:4px; border-style:solid; border-width:2px; border-color:#7babda">

			<div class="form-group row">
				<label for="font_file" class="form-label col-md-3">Image file</label>
				<div class="col-md-9">   
					<input type="file" name="img_file" id="img_file" class="form-control-file" >
				</div>
			</div>


			<div class="form-group row">
				<label for="name" class="form-label col-md-3">Name</label>
				<div class="col-md-9">   
					<input type="text" name="name" id="name" class="form-control" placeholder="Name of output .C files (E.g: red_flower)">
				</div>
			</div>

			<div class="form-group row">
				<label for="transp" class="col-md-3 col-form-label">Transperency</label>
				<div class="col-md-9">
				    <select name="transp" id="transp">
				        <option value="none">None</option>
				        <option value="alpha" >Alpha byte</option>
				        <option value="chroma">Chroma keyed</option>
				    </select><br>
				    <p class="text-mute" style="font-size:14px;"><strong style="font-size:14px;">Alpha byte</strong> Add a 8 bit Alpha value to every pixel<br>
				                            <strong style="font-size:14px;">Chroma keyed</strong> Make LV_COLOR_TRANSP (lv_conf.h) pixels to transparent</p>
				</div>
			</div>
			
			<div class="form-group row">
				<label for="bin_or_c" class="col-md-3 col-form-label">Output format</label>
				<div class="col-md-9">
				    <select name="format" id="format">
				        <option value="c_array">C array</option>
				        <option value="bin_rgb332">Binary RGB332</option>
				        <option value="bin_rgb565">Binary RGB565</option>
				        <option value="bin_rgb888">Binary RGB888</option>
				    </select><br>
				    <p class="text-mute" style="font-size:14px;"><strong style="font-size:14px;">Alpha byte</strong> Add a 8 bit Alpha value to every pixel<br>
				                            <strong style="font-size:14px;">Chroma keyed</strong> Make LV_COLOR_TRANSP (lv_conf.h) pixels to transparent</p>
				</div>
			</div>
			
			<div class="form-group">
				<input type="submit" value="Convert" name="submit" class="btn btn-primary btn-lg">
			</div>
		</form>


	</div>
</div>

<script>
function validate_font_conv_form() {
	return true;
		
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

