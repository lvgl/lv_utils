# Utilities for use with LittlevGL

## 5.3- font converter

The old 5.x font converter (using PHP) was removed in January 2020 as it is no longer compatible with modern versions of LittlevGL. The last commit containing it is [`ec7d2676b36a27fa13b21162aca1476ad8456ec5`](https://github.com/lvgl/lv_utils/tree/ec7d2676b36a27fa13b21162aca1476ad8456ec5).

## Overview

This repository houses a number of converter utilities for LittlevGL. Note that the [6.0+ TTF/OTF font converter](https://littlevgl.com/ttf-font-to-c-array) has its own repository at https://github.com/littlevgl/lv_font_conv.

* Image converter: https://littlevgl.com/image-to-c-array
* Hex font converter

Here you you can see how they work or download them to run offline.

## Get started

This guide shows how to use the converters offline in a command line on **Linux**

1. Install PHP: `sudo apt-get install php7.2-cli`
2. Intall extensions

2.a For the Image manipulator: `sudo apt-get install php7.2-gd`

3. Clone or download the lv_utils repository: `git clone https://github.com/littlevgl/lv_utils.git`
4. Go to the *lv_utils* directory.

For **Mac OSX** using **Homebrew**

Because the php version installed by xcode might not have the GD graphics library installed, you need to install the Homebrew version.  Once installed, you will need to find where it was installed and reference the complete path to php when running the commands shown on this page.  The example below in step 4 happened to be the installation location on my Mac for php version 7.3.3.

1. Install xcode: install via the app store
2. Install xcode CLI tools: xcode-select --install
3. Install PHP: brew install php
4. Check the install: /usr/local/Cellar/php/7.3.3/bin/php --version
5. Continue with step 3 in the installation instructions for Linux

## Run the Image converter offline

1. Copy a BMP, JPG or PNG file you want to use into the *lv_utils* directory
2. Run the script: `php img_conv_core.php "name=wallpaper&img=red_flower.png"`
3. Chek the created C file in *lv_utils* directory.

The required arguments of the script:

- **name** name of the output file and image
- **img** an image file

Optional arguments:

- **cf** color format. Possible values are: `true_color`, `true_color_alpha`, `true_color_chroma`, `indexed_1`, `indexed_2`, `indexed_4`, `indexed_8`, `alpha_1`, `alpha_2`, `alpha_4`, `alpha_8`, `raw`, `raw_alpha`, `raw_chroma`. The default is: `true_color`.
- **format** C array or Binary output. Possible values are: `c_array`, `bin_332`, `bin_565`, `bin_565_swap`, `bin_888`. Default is: `c_array`.

**Note:** You may need to increase `memory_limit` in `php.ini` if PHP reports an error similar to this:
```text
PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted (tried to allocate 21570880 bytes) in lv_utils/img_conv_core.php`
```

### Example with all options

Convert a *bunny.png* with alpha for all pixels to C array:
`php img_conv_core.php "name=icon&img=bunny.png&format=c_array&cf=true_color_alpha"`

### Using the generated image in LittlevGL

* For C arrays
   - Copy the result C file into your LittlevGL project
   - In a C file of your application declare the image as: `extern const lv_img_t my_image_name;` or `LV_IMG_DECLARE(my_image_name);`
   - Set the image for an lv_img object: `lv_img_set_src(img1, &my_image_name);`
* For externally binary files (e.g. SD card)
   - Set up a new driver. To learn more read the [Tutorial](https://github.com/littlevgl/lv_examples/blob/master/lv_tutorial/6_images/lv_tutorial_images.c).
   - Set the image for an lv_img object: `lv_img_set_src(img1, "S:/path/to/image");`
