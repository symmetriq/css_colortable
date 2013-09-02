<?php
/*====================================================================*\
  CSS Color Table 0.1.2
  Jason Sims <jason@symmetriq.net>
\*====================================================================*/

//-----------------------------------------------
// Settings
//-----------------------------------------------

// the name of the CSS file you want to read
$file = 'style.css';

// default color value to sort by
$sort = 'l';

// uncomment to reverse sort order
//$reverse_sort = true;

/*-------------------------*\
  Available Sort Options
-----------------------------
  'hex'     RGB as hex value
  'r'       red
  'g'       green
  'b'       blue
  'hue'     hue (degrees)
  'hsl_s'   HSL saturation
  'l'       HSL luminance
  'hsv_s'   HSV saturation
  'v'       HSV value
\*-------------------------*/

//======================================================================

// don't modify anything past this point unless you know what you're doing

// compatibility for PHP 4 (since str_split requires PHP 5)
if (!function_exists('str_split')) {
	function str_split($string,$split_length=1) {
		$count = strlen($string);
		if ($split_length < 1) {
			return false;
		} elseif ($split_length > $count) {
			return array($string);
		} else {
			$num = (int)ceil($count/$split_length);
			$ret = array();
			for ($i=0; $i < $num; $i++) {
				$ret[] = substr($string,$i*$split_length,$split_length);
			}
			return $ret;
		}
	}
}

$colors = array();
$rows = array();

$sort_name = array(
	'hex' => 'hex',
	'r' => 'red',
	'g' => 'green',
	'b' => 'blue',
	'hue' => 'hue',
	'hsl_s' => 'HSL saturation',
	'l' => 'luminance',
	'hsv_s' => 'HSV saturation',
	'v' => 'HSV value'
);

$sort_option = isset($_GET['sort']) ? $_GET['sort'] : $sort;

$contents = file_get_contents($file);

preg_match_all('/#([0-9A-F]{3,6})/i', $contents, $matches);

foreach ($matches[1] as $css_color) {

	// expand to full hex color if using CSS shorthand
	if (strlen($css_color) == 3) {
	  $hexParts = '';
		for ($i = 0; $i < 3; $i++) {
			$hexParts .= substr($css_color, $i, 1) . substr($css_color, $i, 1);
		}
		$hex = $hexParts;
	} else {
		$hex = $css_color;
	}

	$hex = strtoupper($hex);

	// get color as decimal for ID and linear sort
	$key = hexdec($hex);

	if (!array_key_exists($key, $colors)) {
		$colors[$key] = get_color_info($hex);
	}

	unset($hex);

}

//-------------------------
// Sort color list
//-------------------------

function custom_sort($a, $b) {

	global $sort_option, $reverse_sort;

	if ($b[$sort_option] == $a[$sort_option]) {
		return 0;
	}

	if (isset($reverse_sort)) {
		return ($b[$sort_option] < $a[$sort_option]) ? -1 : 1;
	} else {
		return ($b[$sort_option] > $a[$sort_option]) ? -1 : 1;
	}

}

if ($sort_option == 'hex') {
	if (isset($reverse_sort)) {
		krsort($colors);
	} else {
		ksort($colors);
	}
} else {
	usort($colors, 'custom_sort');
}

//-------------------------
// Build color table rows
//-------------------------

// row output format
$row_format = <<<EOF
	<tr class="shade%1\$s">
		<td class="swatch" style="background-color: #%2\$s">&nbsp;</td>
		<td>#%2\$s</td>
		<td>rgb(%3\$s,%4\$s,%5\$s)</td>
		<td>hsl(%6\$s,%7\$s,%8\$s)</td>
		<td>hsv(%6\$s,%9\$s,%10\$s)</td>
	</tr>
EOF;

foreach ($colors as $key => $values) {
	// make current row shade the first arg
	array_unshift($values, ($i % 2 == 0 ? 1 : 2));
	$rows[] = vsprintf($row_format, $values);
	$i++;
}

$table_rows = implode("\n", $rows);

//-------------------------
// Build menu
//-------------------------

foreach ($sort_name as $key => $value) {
	if ($key == $sort_option) {
		$menu_items[] = '<div class="button">' . $value . '</div>';
	} else {
		$menu_items[] = '<a href="?sort=' . $key . '" class="button">' . $value . '</a>';
	}
}

$menu = implode("\n\t", $menu_items);

//-------------------------
// Output
//-------------------------

echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CSS Colors in "$file"</title>
	<style type="text/css">
	body {
		background-color: #fafafa;
		margin: 15px;
		font-family: Lucida Grande, Verdana, Arial, Helvetica, sans-serif;
		font-size: 14px;
	}

	h1 {
		font-size: 22px;
	}

	table {
		border: 1px solid black;
		border-spacing: 0;
	}

	tr.shade1 td { background-color: #f0f0f0 }

	tr.shade2 td { background-color: #f7f7f7 }

	th {
		background-color: #000;
		color: #fff;
		padding: 5px 10px;
	}

	td {
		font-family: Monaco, Courier;
		font-size: 13px;
		padding: 5px 10px;
		text-align: right;
	}

	td.swatch { width: 50px }

	div.menu { margin-bottom: 15px }

	div.label {
		float: left;
		padding: 3px;
		margin-right: 5px;
		font-weight: bold;
	}

	a.button {
		text-decoration: none;
		background-color: #DEEBF1;
		color: #182731;
	}

	a.button:hover,
	a.button:active {
		background-color: #85A0B0;
		color: #F1FAFF;
	}

	div.button {
		background-color: #182731;
		color: #F1FAFF;
	}

	.button {
		float: left;
		margin-right: 5px;
		border: 1px solid #2D4A59;
		padding: 3px;
	}

	div.clear { clear: both }

	</style>
</head>
<body>

<h1>CSS Colors in "$file"</h1>

<div class="menu">
	<div class="label">Sorted by</div>
	$menu
	<div class="clear"></div>
</div>

<table>
	<tr>
		<th></th>
		<th>Hex</th>
		<th>RGB</th>
		<th>HSL</th>
		<th>HSV</th>
	</tr>
$table_rows
</table>

</body>
</html>
EOF;

//-------------------------
// Get color info
//-------------------------

function get_color_info($hex) {

	// argument must be a 6 character hex string

	//-------------------------
	// Get RGB values
	//-------------------------

	$rgb_parts = str_split($hex, 2);

	foreach ($rgb_parts as $k => $h) {
		$rgb_parts[$k] = hexdec($h);
	}

	list($r, $g, $b) = $rgb_parts;

	//-------------------------
	// Get HSL and HSV values
	//-------------------------

	// hue is the same in both models, so we just grab it from the HSL

	$hsl_parts = rgb_to_hsl($r, $g, $b);
	list($h, $hsl_s, $l) = $hsl_parts;

	$hsv_parts = rgb_to_hsv($r, $g, $b);
	list(, $hsv_s, $v) = $hsv_parts;

	//-------------------------
	// Return
	//-------------------------

	return array(
		'hex' => $hex,		// RGB as hex
		'r' => $r,			// red
		'g' => $g,			// green
		'b' => $b,			// blue
		'hue' => $h,		// hue
		'hsl_s' => $hsl_s,	// HSL saturation
		'l' => $l,			// HSL luminance
		'hsv_s' => $hsv_s,	// HSV saturation
		'v' => $v			// HSV value
	);

}

//==============================
// Color Conversion Algorithms
//==============================

// adapted from: http://www.easyrgb.com/math.html

//-------------------------
// RGB to HSL
//-------------------------

function rgb_to_hsl($r, $g, $b) {

	$r = $r / 255;
	$g = $g / 255;
	$b = $b / 255;

	$rgb_min = min($r, $g, $b);
	$rgb_max = max($r, $g, $b);
	$delta_max = $rgb_max - $rgb_min;

	$l = ($rgb_max + $rgb_min) / 2;

	if ($delta_max == 0) {
		// gray; hue is irrelevant and saturation is 0
		$h = 0;
		$s = 0;
	} else {

		if ($l < 0.5) {
			$s = $delta_max / ($rgb_max + $rgb_min);
		} else {
			$s = $delta_max / (2 - $rgb_max - $rgb_min);
		}

		$delta_r = ((($rgb_max - $r) / 6) + ($rgb_max / 2)) / $delta_max;
		$delta_g = ((($rgb_max - $g) / 6) + ($rgb_max / 2)) / $delta_max;
		$delta_b = ((($rgb_max - $b) / 6) + ($rgb_max / 2)) / $delta_max;

		// set hue based on which RGB value was highest
		switch ($rgb_max) {
			case $r:
				$h = $delta_b - $delta_g;
				break;
			case $g:
				$h = (1/3) + $delta_r - $delta_b;
				break;
			case $b:
				$h = (2/3) + $delta_g - $delta_r;
				break;
		}

		// normalize hue value
		if ($h < 0) $h++;
		if ($h > 1) $h--;

	}

	// hue in degrees, saturation/luminance as percentage
	$h = round($h * 360);
	$s = round($s / 1 * 100);
	$l = round($l / 1 * 100);

	return array($h, $s, $l);

}

//-------------------------
// RGB to HSV (aka HSB)
//-------------------------

function rgb_to_hsv($r, $g, $b) {

	$r = $r / 255;
	$g = $g / 255;
	$b = $b / 255;

	$rgb_min = min($r, $g, $b);
	$rgb_max = max($r, $g, $b);
	$delta_max = $rgb_max - $rgb_min;

	$v = $rgb_max;

	if ($delta_max == 0) {
		// gray; hue is irrelevant and saturation is 0
		$h = 0;
		$s = 0;
	} else {

		$s = $delta_max / $rgb_max;

		$delta_r = ((($rgb_max - $r) / 6) + ($delta_max / 2)) / $delta_max;
		$delta_g = ((($rgb_max - $g) / 6) + ($delta_max / 2)) / $delta_max;
		$delta_b = ((($rgb_max - $b) / 6) + ($delta_max / 2)) / $delta_max;

		// set hue based on which RGB value was highest
		switch ($rgb_max) {
			case $r:
				$h = $delta_b - $delta_g;
				break;
			case $g:
				$h = (1/3) + $delta_r - $delta_b;
				break;
			case $b:
				$h = (2/3) + $delta_g - $delta_r;
				break;
		}

		// normalize hue value
		if ($h < 0) $h++;
		if ($h > 1) $h--;

	}

	// hue in degrees, saturation/value as percentage
	$h = round($h * 360);
	$s = round($s / 1 * 100);
	$v = round($v / 1 * 100);

	return array($h, $s, $v);

}
?>
